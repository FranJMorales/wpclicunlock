<?php
/*
Plugin Name: ClicUnlock
Description: Agrega un pop up que bloquea la navegación hasta la interacción configurada
Version: 1.3.1
Author: <a href="https://www.fj.mk">FJ</a> | <a href="https://www.digitableoslutions.com">Digitable Solutions</a>
*/

// Agregar o quitar la funcionalidad ClicUnlock según la opción seleccionada
function toggle_tp_gateway() {
    $hide_for_logged_in_users = get_option('hide_for_logged_in_users', 'off');

    // No mostrar el script si el usuario ha iniciado sesión y la opción está activada
    if ($hide_for_logged_in_users === 'on' && is_user_logged_in()) {
        return;
    }
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




add_action('wp', 'toggle_tp_gateway');

function add_tp_gateway() {
    $redirect_time = get_option('redirect_time', '3') * 1000;  // Convertir a milisegundos
	$cookie_expire_time = get_option('cookie_expire_time', '5') * 60 * 1000;  // Convertir minutos a milisegundos
    $apply_cookie_site_wide = get_option('apply_cookie_site_wide', 'off') === 'on' ? 'true' : 'false';
	$enable_countdown_overlay = get_option('enable_countdown_overlay', 'off');
	$disable_redirect = get_option('disable_redirect', 'off');
	$pixel_value = get_option('pixel_value', '800');  // Obtiene el valor de píxeles


    echo '
    <script type="text/javascript">
	var cookieExpireTime = ' . $cookie_expire_time . ';
    var applyCookieSiteWide = ' . $apply_cookie_site_wide . ';
    var redirectTime = ' . $redirect_time . ';
	var disableRedirect = "' . $disable_redirect . '";
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
  if (window.scrollY >= Math.max(0, (' . $pixel_value . ' - 100)) && typeof __tp_show_gateway !== "undefined") {
    console.log("X");
    __tp_show_gateway();
  }
};



// Verificar si el overlay de cuenta regresiva está activo
    var enableCountdownOverlay = "' . $enable_countdown_overlay . '";

window.onload = function() {
  document.body.insertBefore(tpGateway, document.body.firstChild);
  document.body.insertBefore(tpGatewayBackground, document.body.firstChild);
  document.body.insertBefore(tpGatewayBackgroundBlackout, document.body.firstChild);
  
	// Verificar si el parámetro "expand_article" está presente en la URL
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has("expand_article") && disableRedirect !== \'on\') {
                // Redirigir a la URL original después de 3 segundos
                setTimeout(function() {
                    var originalUrl = window.location.href.split("?")[0];
                    window.location.href = originalUrl;
                }, ' . $redirect_time . ');
            }

        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has("expand_article") && enableCountdownOverlay === \'on\') {
		
		
            // Mostrar overlay HTML
           var overlay = document.createElement("div");
            overlay.setAttribute("id", "countdown-overlay");

            var countdown = document.createElement("div");
            countdown.setAttribute("id", "countdown");
            
            var countdownSubText = document.createElement("div");
            countdownSubText.setAttribute("id", "countdown-subtext");
            countdownSubText.innerHTML = "Mostrando contenido en";

            overlay.appendChild(countdownSubText);
            overlay.appendChild(countdown);
            document.body.appendChild(overlay);

            // Inicializar cuenta regresiva
            var countdown = ' . $redirect_time . ' / 1000; // Asume que $redirect_time está en milisegundos
            var countdownElement = document.createElement("div");
            countdownElement.setAttribute("id", "countdown");
            countdownElement.innerHTML = countdown + " segundos";
            overlay.appendChild(countdownElement);

            // Actualizar cada segundo
            var countdownInterval = setInterval(function() {
                countdown--;
                countdownElement.innerHTML = countdown + " segundos";
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    overlay.style.display = "none";
                }
            }, 1000);
        }
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
        let triggerLength = ' . $pixel_value . ';
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
				let expireTime = (new Date(Date.now() + cookieExpireTime)).toUTCString();
				let cookiePath = applyCookieSiteWide ? "path=/" : "path=" + window.location.pathname;
				let intCookie = "tpgwcc=1; expires=" + expireTime + "; " + cookiePath;
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
     /* General styles */
#countdown-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

#countdown {
    font-size: 36px;
    color: white;
    text-align: center;
}

#countdown-subtext {
    font-size: 24px;
    color: white;
    text-align: center;
    margin-top: 10px;
}

/* Mobile styles */
@media only screen and (max-width: 600px) {
    #countdown {
        font-size: 28px;
    }
    
    #countdown-subtext {
        font-size: 18px;
    }
}

    .tp-gateway {
    display: none;
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
    box-shadow: 0 4px 10px rgb(0 0 0/16%);
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
    text-decoration: none!important;
    transition: all 0.3s ease;
}

.tp-gateway-button:hover {
    transform: scale(1.05);
}
    </style>
    ';
}

// Cambiar el color del botón
function change_button_color() {
    echo '<style>
        .tp-gateway-button {
            background-color: ' . esc_attr(get_option('tp_gateway_button_color', '#2470ce')) . ';
			border-radius: ' . esc_attr(get_option('tp_gateway_button_radius', '30px')) . ';
			color: ' . esc_attr(get_option('tp_gateway_button_text_color', '#FFFFFF')) . ' !important;' . ';
        }
		.tp-gateway {
			bottom: ' . esc_attr(get_option('tp_gateway_button_height', '20vh')) . ';
        }
    </style>';
}
add_action('wp_head', 'change_button_color');

// Cambiar el texto del botón
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
        'ClicUnlock | Configuración',
        'ClickUnlock',
        'manage_options',
        'ClicUnlock',
        'tp_gateway_settings_callback'
    );
}
add_action('admin_menu', 'tp_gateway_settings_page');

// Contenido de la página de configuración
function tp_gateway_settings_callback() {
    ?>
<a href="https://digitablesolutions.com/" target="_blank" style="display:flex; justify-content: center; inline-block; margin-bottom: 20px;">
    <img src="<?php echo plugin_dir_url(__FILE__) . 'banner-image.png'; ?>" alt="Banner" width="500" height="200">
</a>
<div class="wrap" style="position: relative; padding: 20px; box-shadow: 0 0 20px rgba(0,0,0,0.1); border-radius: 10px;">
    <div style="display: flex; align-items: center; justify-content: space-between;">
       <h1 style="margin: 0; font-size: 30px; font-weight: bold;">Configuración | ClicUnlock <span style="color: grey;">v1.3.1</span></h1>
        <a href="https://fj.mk/contacto" class="button-primary" style="border-color: none; background-color: #487400; color: white; font-weight: bold; padding: 10px 20px;">Contacto</a>
    </div>

    <div class="tab-links" style="margin-top: 30px;">
        <a href="#general" class="active tab-link">General</a>
		<a href="#locations" class="tab-link">Ubicaciones</a>
        <a href="#appearance" class="tab-link">Apariencia</a>
        <a href="#advanced" class="tab-link">Avanzado</a>
        <a href="#credits" class="tab-link">Créditos</a>
    </div>

    <form method="post" action="options.php">
        <?php settings_fields('tp_gateway_settings_group'); ?>
        <?php do_settings_sections('tp_gateway_settings_group'); ?>

	<div id="general" class="tab-content active">
		<h2 style="font-size: 1.5em; margin-bottom: 20px;">Opciones Generales</h2>

		<table class="form-table">
			
					<!-- Nuevo campo para controlar la redirección -->
		<tr valign="top">
			<th scope="row">Anular Redirección</th>
			<td>
				<label>
					<input type="checkbox" id="disable_redirect" name="disable_redirect"
						<?php checked(get_option('disable_redirect', 'off'), 'on'); ?>>
					Desactivar la redirección a la URL original
				</label>
				<span class="description">
					Activa esta opción si no deseas que los usuarios sean redirigidos de vuelta a la URL original.<br>
				</span>
			</td>
		</tr>
			<!-- Desactivar script para usuarios logueados -->
		<tr valign="top">
			<th scope="row">Ocultar para usuarios logueados</th>
			<td>
				<label style="display: inline-flex; align-items: center;">
					<input type="checkbox" name="hide_for_logged_in_users" 
						   <?php checked(get_option('hide_for_logged_in_users', 'off'), 'on'); ?>>
				<span class="description">
					Activa esta opción si deseas ocultar el contenido para usuarios que han iniciado sesión.
				</span>
				</label>

			</td>
		</tr>
			
			<!-- Aplica la cookie en todo el sitio -->
		<tr valign="top">
			<th scope="row">Aplicar cookie a todo el sitio</th>
			<td>
				<label style="display: inline-flex; align-items: center;">
					<input type="checkbox" name="apply_cookie_site_wide" style="margin-right: 10px; height: 20px; width: 20px;"
						<?php checked(get_option('apply_cookie_site_wide', 'off'), 'on'); ?>>
				<span class="description">
					Si activas esta opción, se aplicara la cookie a todo el sitio, lo que evita que si el visitante entra en otro artículo, le vuelva a aparecer el botón. <br>Solo aparecerá otra vez cuando el tiempo de la cookie expire.
				</span>
				</label>
			</td>
		</tr>
			
			<!-- Controla el tiempo de expiración de la cookie -->
		<tr valign="top">
			<th scope="row">Tiempo de expiración de la cookie (minutos)</th>
			<td>
				<input type="number" name="cookie_expire_time" style="border: 1px solid #ccc; border-radius: 5px; padding: 5px; width: 70px;" value="<?php echo esc_attr(get_option('cookie_expire_time', '5')); ?>">
				<span class="description">
					Tiempo en minutos antes de que la cookie expire y vuelva a salir el botón.<br>
					Si "Aplicar cookie en todo el sitio" esta desactivado, cada cookie es única para cada artículo, por lo que este tiempo será solo para ese artículo. <br>Si no te funciona limpia el cache/cookies de el sitio y de tu navegador.
				</span>
			</td>
		</tr>


		</table>
		
		
	</div>
		
	<div id="locations" class="tab-content">
		<h2 style="font-size: 1.5em; margin-bottom: 20px;">Ubicaciones</h2>
		<table class="form-table">
			<!-- Activar tpGateway -->
			<tr valign="top">
				<th scope="row">Activar en todo el sitio</th>
				<td>
					<label>
						<input type="checkbox" id="enable_tp_gateway" name="enable_tp_gateway"
							<?php checked(get_option('enable_tp_gateway', 'off'), 'on'); ?>>
						Activar el script en todo el sitio
					</label>
					<span class="description">
						Activa esta opción si deseas que el script funcione en todo el sitio web.
					</span>
				</td>
			</tr>

			<!-- Activar en Entradas -->
			<tr valign="top">
				<th scope="row">Activar en Entradas</th>
				<td>
					<label>
						<input type="checkbox" id="enable_tp_gateway_entries" name="enable_tp_gateway_entries"
							<?php checked(get_option('enable_tp_gateway_entries', 'off'), 'on'); ?>>
						Activar el script solamente en las entradas
					</label>
					<span class="description">
						Activa esta opción si deseas que el script funcione solo en las entradas del blog.
					</span>
				</td>
			</tr>

			<!-- Activar en Páginas -->
			<tr valign="top">
				<th scope="row">Activar en Páginas</th>
				<td>
					<label>
						<input type="checkbox" id="enable_tp_gateway_pages" name="enable_tp_gateway_pages"
						<?php checked(get_option('enable_tp_gateway_pages', 'off'), 'on'); ?>>
						Activar el script solamente en las páginas
					</label>
					<span class="description">
						Activa esta opción si deseas que el script funcione solo en las páginas.
					</span>
				</td>
			</tr>

			<!-- Activar en Categorías -->
			<tr valign="top">
				<th scope="row">Activar en Categorías</th>
				<td>
					<label>
						<input type="checkbox" id="enable_tp_gateway_categories" name="enable_tp_gateway_categories"
							<?php checked(get_option('enable_tp_gateway_categories', 'off'), 'on'); ?>>
						Activar el script solamente en las categorías
					</label>
					<span class="description">
						Activa esta opción si deseas que el script funcione solo en las categorías del blog.
					</span>
				</td>
			</tr>
		</table>
	</div>


	<!-- Sección de Apariencia -->
	<div id="appearance" class="tab-content">
		<h2 style="margin-bottom: 20px; font-size: 1.5em;">Apariencia</h2>

	<table class="form-table" style="width: 100%; max-width: 800px;">
		<!-- Color del botón -->
		<tr valign="top">
			<th scope="row">Color del botón</th>
			<td>
				<input type="color" name="tp_gateway_button_color" style="width: 50px; height: 50px;" value="<?php echo esc_attr(get_option('tp_gateway_button_color', '#2470ce')); ?>">
				<span class="description">
					Cambia el color del botón para que coincida con el estilo de tu sitio web.
				</span>
			</td>
		</tr>


			<!-- Texto del botón -->
			<tr valign="top">
				<th scope="row">Texto del botón</th>
				<td>
					<input type="text" name="tp_gateway_button_text" style="width: 60%; padding: 5px; vertical-align: middle;"
						value="<?php echo esc_attr(get_option('tp_gateway_button_text', 'Expandir para mostrar el artículo completo')); ?>">
					<br>
					<span class="description" style="font-size: 0.9em; display: block; margin-top: 10px; color: #777;">
						Personaliza el texto que aparecerá en el botón.
					</span>
				</td>
			</tr>
		
					<!-- Border Radius -->
		<tr valign="top">
			<th scope="row">Border Radius del botón</th>
			<td>
				<!-- Campo visible para el usuario -->
				<input type="range" min="0" max="50" step="1" id="radiusSlider" 
					   value="<?php echo esc_attr(intval(get_option('tp_gateway_button_radius', '30'))); ?>">
				<span id="sliderValue"><?php echo esc_attr(intval(get_option('tp_gateway_button_radius', '30'))); ?></span>px

				<!-- Campo oculto que enviará el valor al servidor -->
				<input type="hidden" id="hiddenRadius" name="tp_gateway_button_radius" 
					   value="<?php echo esc_attr(get_option('tp_gateway_button_radius', '30px')); ?>">

				<span class="description">Establece el border-radius del botón.</span>
			</td>
		</tr>
			<!-- Para el Color del Texto -->
		<tr valign="top">
			<th scope="row">Color del texto del botón</th>
			<td>
				<input type="color" id="textColor" name="tp_gateway_button_text_color" style="width: 50px; height: 50px;"   
					   value="<?php echo esc_attr(get_option('tp_gateway_button_text_color', '#FFFFFF')); ?>">
				<span class="description">Establece el color del texto del botón.</span>
			</td>
		</tr>

		<!-- Para la Altura -->
		<tr valign="top">
			<th scope="row">Altura del botón</th>
			<td>
				<input type="range" min="10" max="90" step="1" id="heightSlider" 
					   value="<?php echo esc_attr(intval(get_option('tp_gateway_button_height', '20'))); ?>">
				<span id="heightValue"><?php echo esc_attr(intval(get_option('tp_gateway_button_height', '20'))); ?></span>vh

				<input type="hidden" id="hiddenHeight" name="tp_gateway_button_height" 
					   value="<?php echo esc_attr(get_option('tp_gateway_button_height', '20vh')); ?>">

				<span class="description">Establece la altura del botón. Menor sea este valor mas abajo estará.</span>
			</td>
		</tr>	
		
		
		</table>
	</div>




     <div id="advanced" class="tab-content">
          <h2 style="font-size: 1.5em; margin-bottom: 20px;">Opciones Avanzadas</h2>
		 <table class="form-table" style="width: 100%; max-width: 800px;">
				<!-- Option for Redirect Time -->
				<tr valign="top">
					<th scope="row">Tiempo de Redirección (segundos)</th>
					<td>
						<input type="number" name="redirect_time" style="border: 1px solid #ccc; border-radius: 5px; padding: 5px;"
							value="<?php echo esc_attr(get_option('redirect_time', '3')); ?>">
						<span class="description">
							Tiempo en segundos antes de redirigir a la URL original.
							<br>El script redirecciona a tu dominio con el parámetro añadido ?expand_article=1
							<br>Luego hacemos que vuelva a la URL original con esta redirección. Para evitar posibles problemas de anuncios etc.
							<br>El valor en 0 segundos hace que sea casi instantáneo.
						</span>
					</td>
				</tr>
			 
				<!-- Activar Overlay de Cuenta Regresiva -->
				<tr valign="top">
					<th scope="row">Cuenta Regresiva</th>
					<td>
						<label>
							<input type="checkbox" id="enable_countdown_overlay" name="enable_countdown_overlay"
								<?php checked(get_option('enable_countdown_overlay', 'off'), 'on'); ?>>
							Activar el overlay de cuenta regresiva
						</label>
						<span class="description">
							Activa esta opción para mostrar un overlay de cuenta regresiva mientras redirige de vuelta a la URL original, con el tiempo especificado. Vacia el cache de tu web despues de activarlo.<br>Si esta activado, la opción "Anular Redirección" se desactivará.
						</span>
					</td>
				</tr>
			 
			 
			 <!-- Scroll necesario para que salga el boton -->

			<tr valign="top">
				<th scope="row">Valor de píxeles para el desplazamiento</th>
				<td>
					<input type="number" name="pixel_value" value="<?php echo esc_attr(get_option('pixel_value', '800')); ?>">
					<span class="description">
						Establece el valor de píxeles para mostrar el botón. El valor por defecto es 800.
					</span>
				</td>
			</tr>



				<!-- Option for Activating on Specific URLs -->
				<tr valign="top">
					<th scope="row">Activar en URLs Específicas</th>
					<td>
						<label style="display: inline-flex; align-items: center;">
							<input type="checkbox" id="enable_tp_gateway_custom" name="enable_tp_gateway_urls" style="margin-right: 10px; height: 20px; width: 20px;"
								<?php checked(get_option('enable_tp_gateway_urls', 'off'), 'on'); ?>>
						</label>
						<span class="description">
							Si activas esta opción, se desactivarán las otras ubicaciones.<br>Es importante que compruebes que estén las ubicaciones desactivadas o podría dar errores.
						</span>
					</td>
				</tr>

				<!-- Option for Activated URLs -->
				<tr valign="top">
					<th scope="row">URLs Activadas</th>
					<td>
						<textarea name="tp_gateway_activate_urls" rows="15" cols="50" style="border: 1px solid #ccc; border-radius: 5px; padding: 5px;"
							placeholder="https://example.com/page1&#10;https://example.com/page2"><?php echo esc_textarea(get_option('tp_gateway_activate_urls', '')); ?></textarea>
						<span class="description">
							Ingrese las URLs en líneas separadas, máximo 15 URLs.
						</span>
					</td>
				</tr>

		 </table>
      </div>

    <div id="credits" class="tab-content">
        <h2>Créditos</h2>
        <ul>
            <li>TP</li>
            <li>FJ</li>
            <li>Digitable Solutions</li>
            <li></li>
            <li></li>
        </ul>
    </div>

        <?php submit_button(); ?>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        let tabs = document.querySelectorAll('.tab-links a');
        let contents = document.querySelectorAll('.tab-content');
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                contents.forEach(c => c.classList.remove('active'));
                document.querySelector(tab.getAttribute('href')).classList.add('active');
            });
        });
    });
	
	document.addEventListener("DOMContentLoaded", function() {
    const checkboxesGateway = [
        document.getElementById("enable_tp_gateway"),
        document.getElementById("enable_tp_gateway_entries"),
        document.getElementById("enable_tp_gateway_pages"),
        document.getElementById("enable_tp_gateway_custom"),
        document.getElementById("enable_tp_gateway_categories")
    ];
    
    const checkboxesSpecial = [
        document.getElementById("disable_redirect"),
        document.getElementById("enable_countdown_overlay")
    ];

    // Función para desmarcar todas las casillas excepto la que disparó el evento
    function uncheckOthers(checkbox, group) {
        if (checkbox.checked) {
            group.forEach(function(box) {
                if (box !== checkbox) {
                    box.checked = false;
                }
            });
        }
    }

    // Para las casillas de verificación relacionadas con Gateway
    checkboxesGateway.forEach(function(checkbox) {
        checkbox.addEventListener("change", function() {
            uncheckOthers(this, checkboxesGateway);
        });
    });

    // Para las casillas de verificación especiales (Anular redirección y Overlay)
    checkboxesSpecial.forEach(function(checkbox) {
        checkbox.addEventListener("change", function() {
            uncheckOthers(this, checkboxesSpecial);
        });
    });
});

    // Slider del border radius
const slider = document.getElementById("radiusSlider");
    const output = document.getElementById("sliderValue");
    const hiddenField = document.getElementById("hiddenRadius");

    slider.addEventListener("input", function() {
      output.textContent = slider.value;
      hiddenField.value = slider.value + 'px';
    });
	
	  // Para la Altura del botón
    const heightSlider = document.getElementById("heightSlider");
    const heightOutput = document.getElementById("heightValue");
    const hiddenHeightField = document.getElementById("hiddenHeight");

    heightSlider.addEventListener("input", function() {
        heightOutput.textContent = heightSlider.value;
        hiddenHeightField.value = heightSlider.value + 'vh';
    }); 
	

</script>

<style>
	
		#heightSlider {
        width: 300px;
    }
	    #radiusSlider {
        width: 300px;
    }
    .tab-content {
        display: none;
        margin-top: 20px;
        background: #f9f9f9;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .tab-content.active {
        display: block;
    }
    .tab-links {
        border-bottom: 2px solid #ccc;
        padding-bottom: 10px;
    }
    .tab-link {
        padding: 10px 20px;
        margin-right: 10px;
        display: inline-block;
        border: 1px solid transparent;
        border-radius: 5px;
        text-decoration: none;
		font-size: 18px;
        color: #333;
        font-weight: 600;
    }
    .tab-link.active {
        border-color: #487400;
        background-color: #f9f9f9;
    }
    .form-table th {
        font-size: 1.2em;
        color: #333;
        padding: 15px 0;
        border-bottom: 1px solid #ccc;
    }
    .form-table td {
        padding: 15px 0;
        border-bottom: 1px solid #ccc;
    }
    .form-table input[type="checkbox"] {
        height: 20px;
        width: 20px;
        margin-right: 10px;
    }
    .form-table input[type="color"],
    .form-table input[type="text"] {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 5px;
    }
    .description {
        font-size: 0.9em;
        color: #777;
        margin-top: 10px;
        display: block;
    }
</style>


    <?php
}


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
	register_setting('tp_gateway_settings_group', 'hide_for_logged_in_users');
	register_setting('tp_gateway_settings_group', 'redirect_time');
	register_setting('tp_gateway_settings_group', 'apply_cookie_site_wide');
	register_setting('tp_gateway_settings_group', 'cookie_expire_time');
	register_setting('tp_gateway_settings_group', 'enable_countdown_overlay');
	register_setting('tp_gateway_settings_group', 'disable_redirect');
	register_setting('tp_gateway_settings_group', 'tp_gateway_button_animation');
	register_setting('tp_gateway_settings_group', 'tp_gateway_button_radius');
	register_setting('tp_gateway_settings_group', 'tp_gateway_button_height');
	register_setting('tp_gateway_settings_group', 'tp_gateway_button_text_color');
	register_setting('tp_gateway_settings_group', 'pixel_value');


}
add_action('admin_init', 'tp_gateway_register_settings');
