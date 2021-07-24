//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    exit;
}

class penh_hook_SoldierThemeHook extends _HOOK_CLASS_
{

    /* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'soldierRow' =>
  array (
    0 =>
    array (
      'selector' => 'div.ipsGrid',
      'type' => 'add_inside_end',
      'content' => '
<div class="ipsType_center ipsPad_half ipsResponsive_hidePhone">
    {{if $soldier->isTPRd() == 2}}
        <span data-ipstooltip _title="{lang="request_approved"}"><i style="color: #249168;" class="fa fa-window-close"></i></span>
    {{elseif $soldier->isTPRd() == 1}}
        <span data-ipstooltip _title="{lang="request_pending"}"><i style="color: #d9d900;" class="fa fa-window-close-o"></i></span>
    {{endif}}
</div>
      ',
    ),
  ),
  'soldier' =>
  array (
    0 =>
    array (
      'selector' => 'div.ipsColumns.ipsColumns_noSpacing.ipsColumns_collapseTablet > div.ipsColumn.ipsColumn_fixed.ipsColumn_veryWide > div.cWidgetContainer.ipsPad > ul.ipsList_reset',
      'type' => 'add_inside_end',
      'content' => '
{{if \IPS\Application\Module::get(\'perscom\', \'awards\', \'front\')->visible}}
{{$awards = $soldier->getHighlightedAwards();}}
{{if \count($awards) > 0}}
    <li class="ipsWidget ipsWidget_vertical ipsBox">
        <h2 class="ipsWidget_title ipsType_reset">{lang="penh_soldier_medals"}</h2>
        <div class="ipsWidget_inner ipsPad">
            <ul class="ipsDataList ipsDataList_reducedSpacing">
                <li class="ipsDataItem ipsType_center">
                    {{foreach $awards as $award}}
                        <span data-ipstooltip _title="{$award->name}">
                            <img width="120px" height="auto" src="{file=\'$award->image\'}">
                        </span>
                    {{endforeach}}
                </li>
            </ul>
        </div>
    </li>
{{endif}}
{{endif}}',
    ),
    1 =>
    array (
      'selector' => 'div.ipsPageHeader.ipsClearfix',
      'type' => 'add_after',
      'content' => '
{{if $soldier->uniform === null and !empty(settings.penh_personnel_default_uniform)}}
{{$defaultUniform = settings.penh_personnel_default_uniform;}}
    <div class=\'ipsPerscomSoldierUniform_container {{if settings.perscom_settings_personnel_layout_uniform_mobile == "0"}}ipsResponsive_hidePhone{{endif}}\'>
        <img src="{file=\'$defaultUniform\' extension=\'penh_DefaultUniform\'}" class=\'{{if \IPS\perscom\Helpers\Mobile::isMobile()}}ipsPerscomSoldierUniformFill{{else}}{setting="perscom_settings_personnel_layout_uniform_aspect"}{{endif}}\' title="{$soldier->_title}" style="height: {{if \IPS\perscom\Helpers\Mobile::isMobile()}}200{{else}}{{if settings.perscom_settings_personnel_layout_uniform_height}}{setting="perscom_settings_personnel_layout_uniform_height"}{{else}}200{{endif}}{{endif}}px;" />
    </div>
{{endif}}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */


}
