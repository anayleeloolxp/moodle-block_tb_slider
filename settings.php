<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Slider Settings.
 *
 * @package block_tb_slider
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/blocks/tb_slider/lib.php');

    $setting = new admin_setting_configtext(
        'block_tb_slider/license',
        get_string('license', 'block_tb_slider'),
        get_string('license', 'block_tb_slider'),
        0
    );
    $setting->set_updatedcallback(updateconfslider());
    $settings->add($setting);

    $setting = new admin_setting_configslider('block_tb_slider/settingsjson', '', '', '', PARAM_RAW);
    $settings->add($setting);
}