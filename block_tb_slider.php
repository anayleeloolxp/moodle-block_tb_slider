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
 * Simple slider block for Moodle
 *
 * @package block_tb_slider
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

/**
 * Class block_tb_slider
 */
class block_tb_slider extends block_base {

    /**
     * Initializes block.
     *
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_tb_slider');
    }

    /**
     * Returns content of block.
     *
     * @return stdClass|stdObject
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_content() {
        global $CFG, $bxs;
        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $leeloolxplicense = get_config('block_tb_slider')->license;

        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $postdata = '&license_key=' . $leeloolxplicense;

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => 1,
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            return $this->content;
        }

        $infoleeloolxp = json_decode($output);

        if ($infoleeloolxp->status != 'false') {
            $leeloolxpurl = $infoleeloolxp->data->install_url;
        } else {
            $this->content->text = 'License Key Not Vaild';
            return $this->content;
        }

        $url = $leeloolxpurl . '/admin/Theme_setup/get_sliders_data';
        $postdata = '&license_key=' . $leeloolxplicense;

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => 1,
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $this->content->text = 'License Key Not Vaild';
            return $this->content;
        }

        $settingleeloolxp = json_decode($output);

        $tf = $settingleeloolxp->data->theme_info;

        $this->title = $tf->content_header;

        $this->content = new stdClass;
        $bxslider = false;
        if (isset($tf->config_slider_js) && trim($tf->config_slider_js) === 'bxslider') {
            $bxslider = true;
        }

        if (!isset($bxs)) {
            $bxs = 1;
        } else {
            $bxs++;
        }
        $this->content->text = '<div class="slider"><div id="slides' . $this->instance->id . $bxs . '" ';

        if (!$bxslider) {
            $this->content->text .= 'style="display: none;" class="slides' . $this->instance->id . $bxs . '"';
        } else {
            $this->content->text .= 'class="bxslider bxslider' . $this->instance->id . $bxs . '" style="visibility: hidden;"';
        }
        $this->content->text .= '>';

        $this->content->text .= $this->display_images($bxslider, $settingleeloolxp->data);

        // Navigation Left/Right.
        if (!empty($tf->navigation) && !$bxslider && $settingleeloolxp->data->slides_info) {
            $this->content->text .= '<a href="#" class="slidesjs-previous slidesjs-navigation">';

            $this->content->text .= '<i class="icon fa fa-chevron-left icon-large" aria-hidden="true" aria-label="Prev"></i>';

            $this->content->text .= '</a>';

            $this->content->text .= '<a href="#" class="slidesjs-next slidesjs-navigation">';

            $this->content->text .= '<i class="icon fa fa-chevron-right icon-large" aria-hidden="true" aria-label="Next"></i>';

            $this->content->text .= '</a>';
        }

        $this->content->text .= '</div></div>';

        if (!empty($tf->base_width) and is_numeric($tf->base_width + 0)) {
            $width = $tf->base_width + 0;
        } else {
            $width = 940;
        }

        if (!empty($tf->base_height) and is_numeric($tf->base_height + 0)) {
            $height = $tf->base_height + 0;
        } else {
            $height = 528;
        }

        if (!empty($tf->slide_interval) and is_numeric($tf->slide_interval + 0)) {
            $interval = $tf->slide_interval + 0;
        } else {
            $interval = 5000;
        }

        if (!empty($tf->slide_effect)) {
            $effect = $tf->slide_effect;
        } else {
            $effect = 'fade';
        }

        if (!empty($tf->pagination)) {
            $pag = true;
        } else {
            $pag = false;
        }

        if (!empty($tf->auto_play_slides)) {
            $autoplay = true;
        } else {
            $autoplay = false;
        }

        $nav = false;

        if ($bxslider) {
            $this->page->requires->js_call_amd('block_tb_slider/bxslider', 'init',
                $this->bxslider_get_settings($tf, $this->instance->id . $bxs));
        } else {
            $this->page->requires->js_call_amd('block_tb_slider/slides', 'init',
                array($width, $height, $effect, $interval, $autoplay, $pag, $nav, $this->instance->id . $bxs));
        }

        return $this->content;
    }

    /**
     * Get settings for BXSlider JS.
     *
     * @param stdClass|stdObject $config
     * @param int $sliderid
     * @return array
     */
    public function bxslider_get_settings($config, $sliderid) {
        $bxpause = isset($config->slide_interval) ? $config->slide_interval : 5000;
        $bxeffect = isset($config->bx_slide_effect) ? $config->bx_slide_effect : 'fade';
        $bxspeed = isset($config->transition_duration) ? $config->transition_duration : 500;
        $bxcaptions = isset($config->image_titles) ? $config->image_titles : 0;
        $bxresponsive = isset($config->responsive_slider) ? $config->responsive_slider : 1;
        $bxpager = isset($config->pager) ? $config->pager : 1;
        $bxcontrols = isset($config->controls) ? $config->controls : 1;
        $bxauto = isset($config->auto) ? $config->auto : 1;
        $bxstopautoonclick = isset($config->stop_on_click) ? $config->stop_on_click : 0;
        $bxusecss = isset($config->is_css) ? $config->is_css : 0;
        return array($sliderid, $bxpause, $bxeffect, $bxspeed, boolval($bxcaptions), boolval($bxresponsive), boolval($bxpager),
            boolval($bxcontrols), boolval($bxauto), boolval($bxstopautoonclick), boolval($bxusecss));
    }

    /**
     * Generate html with slides.
     *
     * @param bool $bxslider
     * @param stdClass|stdObject $data
     * @return string
     */
    public function display_images($bxslider = false, $data) {
        // Get and display images.
        $html = '';
        if ($data->slides_info) {
            foreach ($data->slides_info as $slide) {
                $imageurl = $slide->slide_image;
                if ($bxslider) {
                    $html .= html_writer::start_tag('div', ['class' => 'bxslide']);
                }
                if (!empty($slide->slide_link)) {
                    $html .= html_writer::start_tag('a', array('href' => $slide->slide_link, 'rel' => 'nofollow'));
                }
                $html .= html_writer::empty_tag('img',
                    array('src' => $imageurl,
                        'class' => 'img',
                        'alt' => $slide->slide_image,
                        // Title has been moved to html code.
                        'width' => '100%'));
                if (!empty($slide->slide_link)) {
                    $html .= html_writer::end_tag('a');
                }

                // Display captions in BxSlider mode.
                if ($bxslider) {
                    if ($data->theme_info->image_titles or $data->theme_info->image_descriptions) {
                        $classes = '';
                        if ($data->theme_info->image_titles) {
                            $classes .= ' bxcaption';
                        }
                        if ($data->theme_info->image_descriptions) {
                            $classes .= ' bxdesc';
                        }
                        if ($data->theme_info->slide_caption) {
                            $classes .= ' hideonhover';
                        }
                        $html .= html_writer::start_tag('div', array('class' => 'bx-caption' . $classes));
                        $html .= html_writer::tag('span', $slide->slide_title);
                        $html .= html_writer::tag('p', $slide->slide_description);
                        $html .= html_writer::end_tag('div');
                    }

                    $html .= html_writer::end_tag('div');
                }
            }
        }

        return $html;
    }

    /**
     * This plugin has no global config.
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * We are legion.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Where we can add the block?
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'site' => true,
            'course-view' => true,
            'my' => true,
        );
    }

    /**
     * Hide header of this block when user is not editing.
     *
     * @return bool
     */
    public function hide_header() {
        if ($this->page->user_is_editing()) {
            return false;
        } else {
            return true;
        }
    }
}