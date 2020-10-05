<?php
if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'block_tb_slider/license',
        get_string('license', 'block_tb_slider'),
        get_string('license', 'block_tb_slider'),
        0
    ));
}