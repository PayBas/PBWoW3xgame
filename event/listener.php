<?php

/**
*
* @package XGame extension for PBWoW 3
* @copyright (c) 2014 PayBas
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace paybas\xgame\event;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
    exit;
}

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'paybas.pbwow.modify_process_pf_before' => 'add_xgame',
		);
	}

	public function add_xgame($event)
	{
		$profile_row = $event['profile_row'];
		$tpl_fields = $event['tpl_fields'];
		$avatar = '';
		$faction = 0;

		// Since we usually put "none" as the first option (1), our real options start counting at (2). We don't want that, so we -1 everything.
		$xgame_race = isset($tpl_fields['row']['PROFILE_PB_XGAME_RACE_VALUE_RAW']) ? $profile_row['pb_xgame_race']['value'] - 1 : NULL;
		$xgame_class = isset($tpl_fields['row']['PROFILE_PB_XGAME_CLASS_VALUE_RAW']) ? $profile_row['pb_xgame_class']['value'] - 1 : NULL;
		$xgame_gender = isset($tpl_fields['row']['PROFILE_PB_XGAME_GENDER_VALUE_RAW']) ? $profile_row['pb_xgame_gender']['value'] - 1 : NULL;

		// We dump the -1 value back into the template, so we can assign CSS classes starting from 1
		if ($xgame_race > 0) { $tpl_fields['row']['PROFILE_PB_XGAME_RACE_VALUE_RAW'] = $xgame_race; }
		if ($xgame_class > 0) { $tpl_fields['row']['PROFILE_PB_XGAME_CLASS_VALUE_RAW'] = $xgame_class; }
		if ($xgame_gender > 0) { $tpl_fields['row']['PROFILE_PB_XGAME_GENDER_VALUE_RAW'] = $xgame_gender; }

		// Let's assume that if there is no race defined, we can't do anything interesting
		if ($xgame_race !== NULL)
		{
			// Lets assign factions, based on race. If they are race 5,6 or 7, assign them to the red team (1), or else to the blue team (2)
			$faction = (in_array($xgame_race, array(5,6,7))) ? 1 : 2;

			// Usually, genders are in the form of: 0 = none, 1 = male, 2 = female, but we need a 0/1 map.
			$xgame_gender = max(0, $xgame_gender-1); 

			// This part is where the magic happens. This will depend greatly on the specific game. You might want to add some checks to see
			// if the particular combination of profile fields is valid at all. Look at /ext/paybas/pbwow/core/pbwow.php for inspiration.
			$avatar = $xgame_race . '-' . $xgame_gender;

			// Pass the avatar path/filename back to the main script
			$event['avatar'] = 'xgame/' . $avatar . '.jpg';
	
			// This will prevent all the other PBWoW 3 games from being processed. Use this if you only want your game profile-fields to be processed
			$event['function_override'] = true;
		}
	}
}