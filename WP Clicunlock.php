<?php
/*
Plugin Name: ClicUnlock
Description: Agrega un botón que bloquea la lectura hasta que es pulsado
Version: 1.3
Author: <a href="https://www.fj.mk">FJ</a> | <a href="https://www.digitablesolutions.com">Digitable Solutions</a>
*/

// Agregar o quitar la funcionalidad Clicunlock según la opción seleccionada
function toggle_tp_gateway() {
    $enable_general = get_option('enable_tp_gateway', 'off');
    $enable_entries = get_option('enable_tp_gateway_entries', 'off');
    $enable_pages = get_option('enable_tp_gateway_pages', 'off');
    $enable_categories = get_option('enable_tp_gateway_categories', 'off');
    $enable_urls = get_option('enable_tp_gateway_urls', 'off');

    $current_url = $_SERVER['REQUEST_URI'];
    $current_path = parse_url($current_url, PHP_URL_PATH);

    $should_add_tp_gateway = false;

    if ($enable_urls === 'on') {
        $activate_urls = explode("\n", get_option('tp_gateway_activate_urls', ''));

        foreach ($activate_urls as $url) {
            $url_path = parse_url(trim($url), PHP_URL_PATH);
            
            if ($url_path === $current_path) {
                $should_add_tp_gateway = true;
                break;
            }
        }
    } else {
        if ($enable_general === 'on') {
            $should_add_tp_gateway = true;
        }

        if ($enable_entries === 'on' && is_single()) {
            $should_add_tp_gateway = true;
        }

        if ($enable_pages === 'on' && is_page()) {
            $should_add_tp_gateway = true;
        }

        if ($enable_categories === 'on' && is_category()) {
            $should_add_tp_gateway = true;
        }
    }

    if ($should_add_tp_gateway) {
        add_action('wp_footer', 'add_tp_gateway');
    } else {
        remove_action('wp_footer', 'add_tp_gateway');
    }
}

// Creado y modificado por FJ.MK | Digitable Solutions
add_action('wp', 'toggle_tp_gateway');

function add_tp_gateway() {
    echo '
    <script type="text/javascript">
var tpGatewayBackground = document.createElement("div");
tpGatewayBackground.setAttribute("id", "tp-gateway-background");
tpGatewayBackground.setAttribute("class", "tp-gateway-background");

var tpGatewayBackgroundBlackout = document.createElement("div");
tpGatewayBackgroundBlackout.setAttribute("id", "tp-gateway-background-blackout");
tpGatewayBackgroundBlackout.setAttribute("class", "tp-gateway-background-blackout");

var tpGateway = document.createElement("div");
tpGateway.setAttribute("id", "tp-gateway");
tpGateway.setAttribute("class", "tp-gateway");

gatewayURL = new URL(window.location.href);
gatewayURL.searchParams.append("expand_article", "1");

var tpGatewayButton = document.createElement("a");
var customText = document.createTextNode("");
tpGatewayButton.appendChild(customText);
tpGatewayButton.setAttribute("id", "tp-gateway-button");
tpGatewayButton.setAttribute("class", "tp-gateway-button");
tpGatewayButton.setAttribute("href", gatewayURL.href);

tpGateway.appendChild(tpGatewayButton);

window.tpGwjsonpg = true;

window.onscroll = function() {
  if (window.scrollY >= Math.max(0, (800 - 100)) && typeof __tp_show_gateway !== "undefined") {
    console.log("X");
    __tp_show_gateway();
  }
};

window.onload = function() {
  document.body.insertBefore(tpGateway, document.body.firstChild);
  document.body.insertBefore(tpGatewayBackground, document.body.firstChild);
  document.body.insertBefore(tpGatewayBackgroundBlackout, document.body.firstChild);
};

var versionPixelFired = false;
let isInit = false;
let isGwd = false;
function __tp_init_gateway() {
    isInit = true;
    firepx();
    setInitCookie();
    checkGwd();
}
function setInitCookie() {
    let expireTime = (new Date(Date.now() + 300000)).toUTCString();
    let version = -1;
    if (typeof window.tpgwb !== "undefined" && typeof window.tpgwb.version !== "undefined" && !isNaN(window.tpgwb.version)) {
        version = parseInt(window.tpgwb.version);
    }
    let intCookie = "tpgwv=" + version + "; expires=" + expireTime + "; path=/";
    document.cookie = intCookie;
}
function checkGwd() {
    if (window.hideGatewayButton === true) {
        isGwd = true;
        return;
    }
    if (typeof window.tpgwb !== "undefined" && typeof window.tpgwb.version !== "undefined" && (window.tpgwb.version === -1 || isNaN(window.tpgwb.version))) {
        isGwd = true;
        return;
    }
    let minPageLength = window.innerHeight * 2.5;
    if (getPageLength() <= minPageLength) {
        isGwd = true;
        return;
    }
    let seenVignette = getCookie("tpvignetteviewed");
    let confirmedShowMore = getCookie("tpgwcc");
    if (seenVignette !== "" || confirmedShowMore !== "") {
        isGwd = true;
        return;
    }
}
function __tp_show_gateway() {
    if (isInit !== true) {
        __tp_init_gateway();
    }
    if (isGwd === false) {
        let triggerLength = 800;
        if (typeof window.tpcustscry !== "undefined") {
            triggerLength = window.tpcustscry;
        }
        let gateway = document.getElementById("tp-gateway");
        let gatewaybg = document.getElementById("tp-gateway-background");
        if (!gateway || !gatewaybg) {
            return;
        }
        scrollEventThrottle((scrollPosition, prevScrollPos) => {
            let head = 100;
            let buffer = 2;
            let tail = 50;
            let headStart = triggerLength - head;
            let tailStart = triggerLength;
            let tailEnd = tailStart + tail;
            let bufferEnd = tailEnd + buffer;
            function progress(current, start, stop, max) {
                let p = current - start;
                let d = stop - start;
                return Math.min(Math.max((p / d) * max, 0), max);
            }
            if (scrollPosition < headStart) {
                gateway.style.display = "none";
                gatewaybg.style.display = "none";
            } else {
                gateway.style.display = "flex";
                gatewaybg.style.display = "block";
            }
            if (scrollPosition >= headStart && scrollPosition <= bufferEnd) {
                gatewaybg.style.opacity = progress(scrollPosition, headStart, tailStart, 0.75);
            }
            if (scrollPosition > bufferEnd) {
                if ("scrollBehavior" in document.documentElement.style) {
                    window.scrollTo({
                        top: tailEnd,
                        left: 0,
                        behavior: "instant"
                    });
                } else {
                    window.scrollTo(0, tailEnd);
                }
            }
        });
        let gwClickBtn = document.getElementById("tp-gateway-button");
        if (gwClickBtn) {
            gwClickBtn.onclick = function() {
                let expireTime = (new Date(Date.now() + 300000)).toUTCString();
                let intCookie = "tpgwcc=1; expires=" + expireTime + "; path=" + window.location.pathname;
                document.cookie = intCookie;
            };
        }
    }
}
function firepx() {
    if (!versionPixelFired && typeof window.tpgwb !== "undefined" && typeof window.tpgwb.version !== "undefined") {
        let gatewayVersion = -1;
        if (!isNaN(window.tpgwb.version)) {
            gatewayVersion = parseInt(window.tpgwb.version);
        }
        // Píxel de seguimiento relacionado aquí
        versionPixelFired = true;
    }
}
window.addEventListener("beforeunload", function(e) {
    sessionStorage.setItem("position", window.scrollY);
});
function scrollEventThrottle(fn) {
    let scrollPosition = 0;
    let ticking = false;
    window.addEventListener("scroll", function() {
        let prevScrollPos = scrollPosition;
        scrollPosition = window.scrollY;
        if (!ticking) {
            window.requestAnimationFrame(function() {
                fn(scrollPosition, prevScrollPos);
                ticking = false;
            });
            ticking = true;
        }
    });
}
function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(";");
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === " ") {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
function getPageLength() {
    return Math.max(
        document.body.scrollHeight,
        document.body.offsetHeight,
        document.documentElement.clientHeight,
        document.documentElement.scrollHeight,
        document.documentElement.offsetHeight
    );
}
</script>
   <style>
    .tp-gateway {
    display: none;
    bottom: 20vh;
    left: 0;
    -webkit-box-align: center;
    align-items: center;
    box-sizing: border-box;
    flex-direction: column;
    margin: 0 auto;
    padding: 20px;
    position: fixed;
    text-align: left;
    width: 100%;
    z-index: 99000;
    height: auto;
    opacity: 1;
}
.tp-gateway-background {
    display: none;
    position: fixed;
    top: 0;
    z-index: 99000;
    height: 100vh;
    width: 100vw;
    pointer-events: none;
    background: linear-gradient(transparent,#000);
    opacity: 0;
}


.tp-gateway-button {
    border-radius: 30px;
    box-shadow: 0 4px 10px rgb(0 0 0/16%);
    color: #fff!important;
    display: inline-block;
    text-align: center;
    font-size: 16px;
    text-rendering: optimizeLegibility;
    font-family: -apple-system,arial,helvetica;
    font-weight: 700;
    line-height: 1.6;
    margin: 0 auto;
    min-width: 230px;
    padding: 14px 30px;
    text-decoration: none;
}
    </style>
    ';
}
// Creado y modificado por FJ.MK | Digitable Solutions
// Cambiar el color del botón del ClicUnlock
function change_button_color() {
    echo '<style>
        .tp-gateway-button {
            background-color: ' . esc_attr(get_option('tp_gateway_button_color', '#2470ce')) . ';
        }
    </style>';
}
add_action('wp_head', 'change_button_color');

// Cambiar el texto del botón del ClicUnlock
function change_button_text() {
    $customButtonText = esc_attr(get_option('tp_gateway_button_text', 'Expandir para mostrar el artículo completo'));
    echo '<style>
        .tp-gateway-button::before {
            content: "' . $customButtonText . '";
        }
    </style>';
}
add_action('wp_head', 'change_button_text');

// Agregar una página de configuración en el panel de administración
function tp_gateway_settings_page() {
    add_options_page(
        'Configuración de ClicUnlock',
        'ClicUnlock',
        'manage_options',
        'tp_gateway_settings',
        'tp_gateway_settings_callback'
    );
}
add_action('admin_menu', 'tp_gateway_settings_page');

// Contenido de la página de configuración
function tp_gateway_settings_callback() {
    ?>
    <div class="wrap">
        <h1>Configuración de ClicUnlock</h1>
        <form method="post" action="options.php">
            <?php settings_fields('tp_gateway_settings_group'); ?>
            <?php do_settings_sections('tp_gateway_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Activar ClicUnlock - Por Si Quieres Activar en Todo</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_tp_gateway"
                                <?php checked(get_option('enable_tp_gateway', 'off'), 'on'); ?>>
                            Activar ClicUnlock en el sitio
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Color del botón</th>
                    <td>
                        <input type="color" name="tp_gateway_button_color"
                            value="<?php echo esc_attr(get_option('tp_gateway_button_color', '#2470ce')); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Texto del botón</th>
                    <td>
                        <input type="text" name="tp_gateway_button_text"
                            value="<?php echo esc_attr(get_option('tp_gateway_button_text', 'Expandir para mostrar el artículo completo')); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Activar en Entradas - Solo entradas desactiva el resto de opciones</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_tp_gateway_entries"
                                <?php checked(get_option('enable_tp_gateway_entries', 'off'), 'on'); ?>>
                            Activar en las entradas
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Activar en Páginas - Solo Paginas desactiva el resto de opciones</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_tp_gateway_pages"
                                <?php checked(get_option('enable_tp_gateway_pages', 'off'), 'on'); ?>>
                            Activar en las páginas
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Activar en Categorías - Solo Categorias desactiva el resto de opciones</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_tp_gateway_categories"
                                <?php checked(get_option('enable_tp_gateway_categories', 'off'), 'on'); ?>>
                            Activar en las categorías
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Activar en URLs Específicas</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_tp_gateway_urls"
                                <?php checked(get_option('enable_tp_gateway_urls', 'off'), 'on'); ?>>
                            Activar en URLs específicas - Recuerda que si activas esta opción debes desactivar las demas ¡de lo contrario dara error!
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">URLs Activadas</th>
                    <td>
                        <textarea name="tp_gateway_activate_urls" rows="15" cols="50"
                            placeholder="https://example.com/page1&#10;https://example.com/page2"><?php echo esc_textarea(get_option('tp_gateway_activate_urls', '')); ?></textarea>
                        <p class="description">Ingrese las URLs en líneas separadas maximo 15 URLS.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
// Creado y modificado por FJ.MK | Digitable Solutions
// Registrar opciones de configuración
function tp_gateway_register_settings() {
    register_setting('tp_gateway_settings_group', 'enable_tp_gateway');
    register_setting('tp_gateway_settings_group', 'enable_tp_gateway_entries');
    register_setting('tp_gateway_settings_group', 'enable_tp_gateway_pages');
    register_setting('tp_gateway_settings_group', 'enable_tp_gateway_categories');
    register_setting('tp_gateway_settings_group', 'enable_tp_gateway_urls');
    register_setting('tp_gateway_settings_group', 'tp_gateway_activate_urls');
    register_setting('tp_gateway_settings_group', 'tp_gateway_button_color');
    register_setting('tp_gateway_settings_group', 'tp_gateway_button_text');
}
add_action('admin_init', 'tp_gateway_register_settings');
// Creado y modificado por FJ.MK | Digitable Solutions
?>
