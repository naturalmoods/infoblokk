<?php
/*
Plugin Name: Infoblokk
Description: Jobb oldali fix infoblokk, felül vagy alul megjelenítve.
Version: 1.3
Author: Cre-art Stúdió
*/

if (!defined('ABSPATH')) exit;

// Beállítások regisztrálása
add_action('admin_init', function () {
    register_setting('infoblokk_settings', 'infoblokk_active');
    register_setting('infoblokk_settings', 'infoblokk_position');
    register_setting('infoblokk_settings', 'infoblokk_style');
    register_setting('infoblokk_settings', 'infoblokk_url');
});

// Admin menü létrehozása
add_action('admin_menu', function () {
    add_options_page('Infoblokk Beállítások', 'Infoblokk', 'manage_options', 'infoblokk', 'infoblokk_settings_page');
});

// Admin oldal HTML
function infoblokk_settings_page() {
    ?>
    <div class="wrap">
        <h1>Infoblokk beállítások</h1>
        <form method="post" action="options.php">
            <?php settings_fields('infoblokk_settings'); ?>
            <?php do_settings_sections('infoblokk_settings'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">Aktív</th>
                    <td>
                        <input type="checkbox" name="infoblokk_active" value="1" <?php checked(1, get_option('infoblokk_active'), true); ?>>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Pozíció</th>
                    <td>
                        <select name="infoblokk_position">
                            <option value="top" <?php selected(get_option('infoblokk_position'), 'top'); ?>>Felül</option>
                            <option value="bottom" <?php selected(get_option('infoblokk_position'), 'bottom'); ?>>Alul</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Kép</th>
                    <td>
                        <select name="infoblokk_style">
                            <option value="default" <?php selected(get_option('infoblokk_style'), 'default'); ?>>Széchenyi (Alapértelmezett)</option>
                            <option value="eu_social" <?php selected(get_option('infoblokk_style'), 'eu_social'); ?>>EU Szociális Alap</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">URL</th>
                    <td>
                        <input type="text" name="infoblokk_url" value="<?php echo esc_attr(get_option('infoblokk_url')); ?>" style="width: 400px;">
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Megjelenítés a frontend-en
add_action('wp_footer', 'infoblokk_display');
function infoblokk_display() {
    if (!get_option('infoblokk_active')) return;

    $position = get_option('infoblokk_position', 'top');
    $style = get_option('infoblokk_style', 'default');
    $url = esc_url(get_option('infoblokk_url', '#'));

    if ($style === 'eu_social') {
        $image = plugin_dir_url(__FILE__) . 'img/eu-szocialis-alap.png';
    } else {
        $image = plugin_dir_url(__FILE__) . ($position === 'top' ? 'img/infoblokk_top.png' : 'img/infoblokk_bottom.png');
    }
    $css_position = $position === 'top' ? 'top: 0px;' : 'bottom: 0px;';

    echo '
    <style>
        #infoblokk {
            position: fixed;
            right: 0;
            z-index: 2000;
            padding: 0;
            display: none;
        }
        #infoblokk-close {
            position: absolute;
            top: 0;
            left: -20px;
            background: #333;
            color: #fff;
            padding: 2px 5px;
            cursor: pointer;
            font-weight: bold;
            display: none;
        }
        #infoblokk:hover #infoblokk-close {
            display: block;
        }
    </style>

    <div id="infoblokk" style="'.$css_position.'">
        <span id="infoblokk-close">×</span>
        <a href="'.$url.'"><img src="'.$image.'" alt="Infoblokk"></a>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function(){
        if(!sessionStorage.getItem("infoblokk_closed")){
            document.getElementById("infoblokk").style.display = "block";
        }
        document.getElementById("infoblokk-close").addEventListener("click", function(){
            document.getElementById("infoblokk").style.display = "none";
            sessionStorage.setItem("infoblokk_closed", "1");
        });
    });
    </script>
    ';
}
