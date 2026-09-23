<?php
/*
Plugin Name: Infoblokk
Description: Fix infoblokkok megjelenítése többféle pozícióban.
Version: 1.8
Author: Cre-art Stúdió
*/

if (!defined('ABSPATH')) exit;

function infoblokk_styles() {
    return [
        'stp' => [
            'label' => 'Széchenyi Terv Plusz',
            'top' => 'szechenyi-terv-plusz.webp',
            'bottom' => 'szechenyi-terv-plusz.webp',
        ],
        'erfa' => [
            'label' => 'Európai Regionális Fejlesztési Alap',
            'top' => 'infoblokk_ERFA_felso.webp',
            'bottom' => 'infoblokk_ERFA_also.webp',
        ],
        'esza' => [
            'label' => 'Európai Szociális Alap',
            'top' => 'infoblokk_ESZA_felso.webp',
            'bottom' => 'infoblokk_ESZA_also.webp',
        ],
        'ka' => [
            'label' => 'Kohéziós Alap',
            'top' => 'infoblokk_KA_felso.webp',
            'bottom' => 'infoblokk_KA_also.webp',
        ],
        'esba' => [
            'label' => 'Európai Strukturális és Beruházási Alapok',
            'top' => 'infoblokk_ESBA_felso.webp',
            'bottom' => 'infoblokk_ESBA_also.webp',
        ],
        'kap' => [
            'label' => 'Közös Agrárpolitika (KAP)',
            'top' => 'KAP.webp',
            'bottom' => 'KAP.webp',
        ],
    ];
}

function infoblokk_normalize_style($style) {
    $old_styles = ['default' => 'stp', 'eu_social' => 'esza'];
    $style = $old_styles[$style] ?? $style;

    return isset(infoblokk_styles()[$style]) ? $style : 'stp';
}

function infoblokk_can_go_left($style) {
    $style = infoblokk_styles()[infoblokk_normalize_style($style)];

    return $style['top'] === $style['bottom'];
}

function infoblokk_normalize_side($side, $style) {
    if (infoblokk_normalize_style($style) === 'kap') return 'left';

    return $side === 'left' && infoblokk_can_go_left($style) ? 'left' : 'right';
}

function infoblokk_normalize_position($position, $style) {
    return infoblokk_normalize_style($style) === 'kap' ? 'top' : ($position === 'bottom' ? 'bottom' : 'top');
}

function infoblokk_empty_block() {
    return [
        'active' => 1,
        'position' => 'top',
        'side' => 'right',
        'style' => 'stp',
        'url' => '',
        'disable_close' => 0,
    ];
}

function infoblokk_sanitize_blocks($blocks) {
    if (!is_array($blocks)) return [];

    $clean = [];
    foreach ($blocks as $block) {
        if (!is_array($block)) continue;

        $style = infoblokk_normalize_style($block['style'] ?? 'stp');
        $clean[] = [
            'active' => empty($block['active']) ? 0 : 1,
            'position' => infoblokk_normalize_position($block['position'] ?? 'top', $style),
            'side' => infoblokk_normalize_side($block['side'] ?? 'right', $style),
            'style' => $style,
            'url' => esc_url_raw($block['url'] ?? ''),
            'disable_close' => empty($block['disable_close']) ? 0 : 1,
        ];
    }

    return $clean;
}

function infoblokk_get_blocks() {
    $blocks = get_option('infoblokk_blocks', null);
    if (is_array($blocks)) return $blocks;

    // Régi, egy infoblokkos beállítások átvétele mentésig.
    return [[
        'active' => get_option('infoblokk_active') ? 1 : 0,
        'position' => get_option('infoblokk_position', 'top'),
        'side' => 'right',
        'style' => infoblokk_normalize_style(get_option('infoblokk_style', 'stp')),
        'url' => get_option('infoblokk_url', ''),
        'disable_close' => get_option('infoblokk_disable_close') ? 1 : 0,
    ]];
}

function infoblokk_image_url($style, $position) {
    $style = infoblokk_styles()[infoblokk_normalize_style($style)];

    return plugin_dir_url(__FILE__) . 'img/' . $style[$position === 'bottom' ? 'bottom' : 'top'];
}

// Beállítások regisztrálása
add_action('admin_init', function () {
    register_setting('infoblokk_settings', 'infoblokk_blocks', [
        'sanitize_callback' => 'infoblokk_sanitize_blocks',
    ]);
});

// Admin menü létrehozása
add_action('admin_menu', function () {
    add_options_page('Infoblokk Beállítások', 'Infoblokk', 'manage_options', 'infoblokk', 'infoblokk_settings_page');
});

function infoblokk_block_fields($index, $block) {
    $block = wp_parse_args($block, infoblokk_empty_block());
    ?>
    <div class="infoblokk-admin-block" style="border:1px solid #ccd0d4;background:#fff;padding:16px;margin:0 0 16px;max-width:760px;">
        <p style="margin-top:0;"><strong>Infoblokk</strong> <button type="button" class="button-link-delete infoblokk-remove" style="float:right;">Eltávolítás</button></p>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Aktív</th>
                <td><input type="checkbox" name="infoblokk_blocks[<?php echo esc_attr($index); ?>][active]" value="1" <?php checked(1, $block['active'], true); ?>></td>
            </tr>
            <tr>
                <th scope="row">Pozíció</th>
                <td>
                    <select name="infoblokk_blocks[<?php echo esc_attr($index); ?>][position]">
                        <option value="top" <?php selected($block['position'], 'top'); ?>>Felül</option>
                        <option value="bottom" <?php selected($block['position'], 'bottom'); ?>>Alul</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">Oldal</th>
                <td>
                    <select name="infoblokk_blocks[<?php echo esc_attr($index); ?>][side]">
                        <option value="right" <?php selected($block['side'], 'right'); ?>>Jobbra</option>
                        <option value="left" <?php selected($block['side'], 'left'); ?>>Balra</option>
                    </select>
                    <p class="description">A KAP mindig balra fent, a Széchenyi Terv Plusz mindkét oldalon, a többi mindig jobbra jelenik meg.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Kép</th>
                <td>
                    <select name="infoblokk_blocks[<?php echo esc_attr($index); ?>][style]">
                        <?php foreach (infoblokk_styles() as $style_key => $style) : ?>
                            <option value="<?php echo esc_attr($style_key); ?>" <?php selected(infoblokk_normalize_style($block['style']), $style_key); ?>><?php echo esc_html($style['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">URL</th>
                <td><input type="text" name="infoblokk_blocks[<?php echo esc_attr($index); ?>][url]" value="<?php echo esc_attr($block['url']); ?>" style="width:400px;max-width:100%;"></td>
            </tr>
            <tr>
                <th scope="row">Bezárás gomb elrejtése</th>
                <td><input type="checkbox" name="infoblokk_blocks[<?php echo esc_attr($index); ?>][disable_close]" value="1" <?php checked(1, $block['disable_close'], true); ?>></td>
            </tr>
        </table>
    </div>
    <?php
}

// Admin oldal HTML
function infoblokk_settings_page() {
    $blocks = infoblokk_get_blocks();
    ?>
    <div class="wrap">
        <h1>Infoblokk beállítások</h1>
        <form method="post" action="options.php">
            <?php settings_fields('infoblokk_settings'); ?>
            <?php do_settings_sections('infoblokk_settings'); ?>

            <div id="infoblokk-blocks">
                <?php foreach ($blocks as $index => $block) infoblokk_block_fields($index, $block); ?>
            </div>

            <p><button type="button" class="button" id="infoblokk-add">Új infoblokk</button></p>

            <?php submit_button(); ?>
        </form>

        <script type="text/template" id="infoblokk-template">
            <?php infoblokk_block_fields('__index__', infoblokk_empty_block()); ?>
        </script>
        <script>
        (function () {
            var holder = document.getElementById('infoblokk-blocks');
            var template = document.getElementById('infoblokk-template').innerHTML;

            document.getElementById('infoblokk-add').addEventListener('click', function () {
                holder.insertAdjacentHTML('beforeend', template.replace(/__index__/g, Date.now()));
            });

            holder.addEventListener('click', function (event) {
                if (event.target.classList.contains('infoblokk-remove')) {
                    event.target.closest('.infoblokk-admin-block').remove();
                }
            });
        })();
        </script>
    </div>
    <?php
}

// Megjelenítés a frontend-en
add_action('wp_footer', 'infoblokk_display');
function infoblokk_display() {
    $blocks = infoblokk_get_blocks();
    if (!$blocks) return;

    $html = [
        'right' => ['top' => '', 'bottom' => ''],
        'left' => ['top' => '', 'bottom' => ''],
    ];

    foreach ($blocks as $index => $block) {
        $block = wp_parse_args($block, infoblokk_empty_block());
        if (empty($block['active'])) continue;

        $style = infoblokk_normalize_style($block['style']);
        $position = infoblokk_normalize_position($block['position'], $style);
        $side = infoblokk_normalize_side($block['side'], $style);
        $url = esc_url($block['url'] ?: '#');
        $image = esc_url(infoblokk_image_url($style, $position));
        $key = esc_attr(md5($index . '|' . $position . '|' . $side . '|' . $style . '|' . $url));

        $html[$side][$position] .= '<div class="infoblokk-item' . ($style === 'kap' ? ' infoblokk-item-kap' : '') . '" data-infoblokk-key="' . $key . '">';
        if (empty($block['disable_close'])) {
            $html[$side][$position] .= '<button type="button" class="infoblokk-close" aria-label="Infoblokk bezárása">×</button>';
        }
        $html[$side][$position] .= '<a href="' . $url . '"><img src="' . $image . '" alt="Infoblokk"></a></div>';
    }

    if (!$html['right']['top'] && !$html['right']['bottom'] && !$html['left']['top'] && !$html['left']['bottom']) return;

    echo '
    <style>
        .infoblokk-stack {
            position: fixed;
            z-index: 2000;
            display: flex;
            pointer-events: none;
        }
        .infoblokk-stack-right { right: 0; align-items: flex-end; }
        .infoblokk-stack-left { left: 0; align-items: flex-start; }
        .infoblokk-stack-top { top: 0; flex-direction: column; }
        .infoblokk-stack-bottom { bottom: 0; flex-direction: column-reverse; }
        .infoblokk-item {
            position: relative;
            display: none;
            padding: 0;
            pointer-events: auto;
        }
        .infoblokk-item img { display: block; max-width: 100vw; height: auto; }
        .infoblokk-item-kap { margin: 16px 0 0 16px; }
        .infoblokk-close {
            position: absolute;
            top: 0;
            left: -20px;
            border: 0;
            background: #333;
            color: #fff;
            padding: 2px 5px;
            cursor: pointer;
            font-weight: bold;
            line-height: 1;
            display: none;
        }
        .infoblokk-stack-left .infoblokk-close { left: auto; right: -20px; }
        .infoblokk-item:hover .infoblokk-close,
        .infoblokk-item:focus-within .infoblokk-close { display: block; }
    </style>

    ' . ($html['right']['top'] ? '<div class="infoblokk-stack infoblokk-stack-right infoblokk-stack-top">' . $html['right']['top'] . '</div>' : '') . '
    ' . ($html['right']['bottom'] ? '<div class="infoblokk-stack infoblokk-stack-right infoblokk-stack-bottom">' . $html['right']['bottom'] . '</div>' : '') . '
    ' . ($html['left']['top'] ? '<div class="infoblokk-stack infoblokk-stack-left infoblokk-stack-top">' . $html['left']['top'] . '</div>' : '') . '
    ' . ($html['left']['bottom'] ? '<div class="infoblokk-stack infoblokk-stack-left infoblokk-stack-bottom">' . $html['left']['bottom'] . '</div>' : '') . '

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".infoblokk-item").forEach(function (block) {
            var storageKey = "infoblokk_closed_" + block.getAttribute("data-infoblokk-key");
            if (!sessionStorage.getItem(storageKey)) block.style.display = "block";

            var close = block.querySelector(".infoblokk-close");
            if (close) {
                close.addEventListener("click", function () {
                    block.style.display = "none";
                    sessionStorage.setItem(storageKey, "1");
                });
            }
        });
    });
    </script>
    ';
}
