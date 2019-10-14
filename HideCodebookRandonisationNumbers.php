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
        const OPTION_THRESHOLD = 10;
        public function redcap_every_page_top($project_id) {
                if (isset($project_id) && intval($project_id)>0 && PAGE==='Design/data_dictionary_codebook.php') {

                        // hide long randomisation list info in codebook because is very long and
                        // may even show group allocation sequence (when not blinded)
                        global $Proj, $randomization;

                        if ($randomization) {
                                $randomizationAttributes = Randomization::getRandomizationAttributes();
                                if ($randomizationAttributes !== false) {
                                        $targetField = $randomizationAttributes['targetField'];
                                        $randlistOptions = parseEnum($Proj->metadata[$targetField]['element_enum']);
                                        if (count($randlistOptions) > self::OPTION_THRESHOLD) {
                                                ?>
                                                <script type='text/javascript'>
                                                    (function(document, $) {
                                                        $(document).ready(function() {
                                                            // find the target field in the var names column - ensure 
                                                            // no preceding [ so not just a branching logic match
                                                            var randVarTd = $('td.vwrap').filter(function() {
                                                                return $(this).html().match(/^\s*<?php echo $targetField;?>/);
                                                            });
                                                            randVarTd
                                                                .parent('tr')
                                                                .find('table.ReportTableWithBorder')
                                                                .replaceWith('<div style="margin-top:5px;"><mark>Randomisation numbers hidden</mark></div>'); 
                                                        });
                                                    })(document, jQuery);
                                                </script>
                                                <?php
                                        }
                                }
                        }
                }
        }
}