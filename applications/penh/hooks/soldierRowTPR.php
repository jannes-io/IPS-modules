//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class penh_hook_soldierRowTPR extends _HOOK_CLASS_
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
      'content' => '     <div class="ipsType_center ipsPad_half ipsResponsive_hidePhone">
      {{if $soldier->isTPRd() == 2}}
      <span data-ipstooltip _title="{lang="request_approved"}"><i style="color: #249168;" class="fa fa-window-close"></i></span>
      {{elseif $soldier->isTPRd() == 1}}
      <span data-ipstooltip _title="{lang="request_pending"}"><i style="color: #d9d900;" class="fa fa-window-close-o"></i></span>
      {{endif}}
      </div>
      ',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */


}
