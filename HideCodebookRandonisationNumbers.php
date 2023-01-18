<?php
/**
 * REDCap External Module: Hide Codebook Randomisation Numbers
 * Hides randomisation allocation groups on Codebook page. 
 * Useful when the randomisation allocation field is set up with numbers rather than groups.
 * @author Luke Stevens, Murdoch Children's Research Institute
 */
namespace MCRI\HideCodebookRandonisationNumbers;

use ExternalModules\AbstractExternalModule;
use Randomization;

class HideCodebookRandonisationNumbers extends AbstractExternalModule
{
        const DEFAULT_THRESHOLD = 10;
        const DEFAULT_MESSAGE = "Randomization numbers hidden";
        
        public function redcap_module_system_enable($version) {
                $this->setSystemSetting('system-threshold', ''.self::DEFAULT_THRESHOLD);
                $this->setSystemSetting('system-message', self::DEFAULT_MESSAGE);
        }
        
        public function redcap_every_page_top($project_id) {
                if (isset($project_id) && intval($project_id)>0 && PAGE==='Design/data_dictionary_codebook.php') {

                        // hide long randomisation list info in codebook because is very long and
                        // may even show group allocatiion sequence (when not blinded)
                        global $Proj, $randomization;

                        if ($randomization) {
                                $randomizationAttributes = Randomization::getRandomizationAttributes();
                                if ($randomizationAttributes !== false) {
                                        $targetField = $randomizationAttributes['targetField'];
                                        $randlistOptions = parseEnum($Proj->metadata[$targetField]['element_enum']);
                                        
                                        $threshold = $this->getThreshold();
                                        $message = $this->getMessage();
                                            
                                        if (count($randlistOptions) > intval($threshold)) {
                                                ?>
                                                <script type='text/javascript'>
                                                    (function(document, $) {
                                                        $(document).ready(function() {
                                                            // find the target field in the var names column - ensure 
                                                            $('#codebook-table tr[data-field=<?=$targetField?>]')
                                                                .find('table.ReportTableWithBorder')
                                                                .replaceWith('<div style="margin-top:5px;"><mark><?php echo $message;?></mark></div>');
                                                        });
                                                    })(document, jQuery);
                                                </script>
                                                <?php
                                        }
                                }
                        }
                }
        }
        
        protected function getThreshold() {
                return $this->getSetting('threshold');
        }
        
        protected function getMessage() {
                return $this->getSetting('message');
        }
        
        protected function getSetting($setting) {
                $s = $this->getProjectSetting("project-$setting");
                if (is_null($s) || empty($s)) {
                        $s = $this->getSystemSetting("system-$setting");
                }
                if (is_null($s) || empty($s)) {
                        $s = '';
                        if ($setting==='threshold') { $s = self::DEFAULT_THRESHOLD; }
                        if ($setting==='message') { $s = self::DEFAULT_MESSAGE; }
                }
                return \REDCap::escapeHtml($s);
        }
}