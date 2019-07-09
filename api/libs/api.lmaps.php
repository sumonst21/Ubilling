<?php

/*
 * Leaflet maps API implementation
 */

/**
 * Returns leaflet maps empty container
 * 
 * @param string $width
 * @param string $height
 * @param string $id
 * 
 * @return string
 */
function generic_MapContainer($width = '', $height = '', $id = '') {
    $width = (!empty($width)) ? $width : '100%';
    $height = (!empty($height)) ? $height : '800px;';
    $id = (!empty($id)) ? $id : 'ubmap';
    $result = wf_tag('div', false, '', 'id="' . $id . '" style="width: 100%; height:800px;"');
    $result .= wf_tag('div', true);
    return ($result);
}

/**
 * Translates yandex to google icon code
 * 
 * @param string $icon
 * @return string
 */
function lm_GetIconUrl($icon) {
    $result = '';
    switch ($icon) {
        case 'twirl#lightblueIcon':
            $result = 'skins/mapmarks/blue.png';
            break;
        case 'twirl#lightblueStretchyIcon':
            $result = 'skins/mapmarks/blue.png';
            break;
        case 'twirl#redStretchyIcon':
            $result = 'skins/mapmarks/red.png';
            break;
        case 'twirl#yellowIcon':
            $result = 'skins/mapmarks/yellow.png';
            break;
        case 'twirl#greenIcon':
            $result = 'skins/mapmarks/green.png';
            break;
        case 'twirl#pinkDotIcon':
            $result = 'skins/mapmarks/pink.png';
            break;
        case 'twirl#brownIcon':
            $result = 'skins/mapmarks/brown.png';
            break;
        case 'twirl#nightDotIcon':
            $result = 'skins/mapmarks/darkblue.png';
            break;
        case 'twirl#redIcon':
            $result = 'skins/mapmarks/red.png';
            break;
        case 'twirl#orangeIcon':
            $result = 'skins/mapmarks/orange.png';
            break;
        case 'twirl#greyIcon':
            $result = 'skins/mapmarks/grey.png';
            break;
        case 'twirl#buildingsIcon':
            $result = 'skins/mapmarks/build.png';
            break;
        case 'twirl#houseIcon':
            $result = 'skins/mapmarks/house.png';
            break;
        case 'twirl#campingIcon':
            $result = 'skins/mapmarks/camping.png';
            break;

        default :
            $result = 'skins/mapmarks/blue.png';
            show_warning('Unknown icon received: ' . $icon);
            break;
    }
    return ($result);
}

/**
 * Returns placemark code
 * 
 * @param string $coords
 * @param string $title
 * @param string $content
 * @param string $footer
 * @param string $icon
 * @param string $iconlabel
 * @param bool $canvas
 * 
 * @return string
 */
function generic_MapAddMark($coords, $title = '', $content = '', $footer = '', $icon = 'twirl#lightblueIcon', $iconlabel = '', $canvas = false) {
    $result = '';

    $title = str_replace('"', '\"', $title);
    $content = str_replace('"', '\"', $content);
    $footer = str_replace('"', '\"', $footer);
    $iconCode = '';
    $iconDefines = '';

    if (!empty($icon)) {
        $iconFile = lm_GetIconUrl($icon);
        $iconDefines .= "var LeafIcon = L.Icon.extend({
		options: {
			iconSize:     [42, 42],
			iconAnchor:   [22, 41],
			popupAnchor:  [-3, -44]
		}
	});
        

      	var customIcon = new LeafIcon({iconUrl: '" . $iconFile . "'});

";
        $iconCode .= ', {icon: customIcon}';
    }

    $result .= $iconDefines;
    $result .= 'var placemark=L.marker([' . $coords . ']' . $iconCode . ').addTo(map)
		.bindPopup("<b>' . $title . '</b><br />' . $content . '<br>' . $footer . '");';

    if (!empty($content)) {
        $result .= 'placemark.bindTooltip("' . $content . '", { sticky: true});';
    }

    return($result);
}

/**
 * Returns map circle
 * 
 * @param string $coords - map coordinates
 * @param int $radius - circle radius in meters
 * @param string $content 
 * 
 * @return string
 *  
 */
function generic_MapAddCircle($coords, $radius, $content = '', $hint = '') {
    $result = '
           var circle = L.circle([' . $coords . '], {
                    color: \'#009d25\',
                    fillColor: \'#00a20b55\',
                    fillOpacity: 0.5,
                    radius: ' . $radius . '
                }).addTo(map);
            ';
    if (!empty($content)) {
        $result .= 'circle.bindPopup("' . $content . '");';
    }

    if (!empty($hint)) {
        $hint = str_replace('"', '\"', $hint);
        $result .= 'circle.bindTooltip("' . $hint . '", { sticky: true});';
    }


    return ($result);
}

/**
 * Initalizes google maps API with some params
 * 
 * @param string $center
 * @param int $zoom
 * @param string $type
 * @param string $placemarks
 * @param bool $editor
 * @param string $lang
 * @param string $container
 * 
 * @return string
 */
function generic_MapInit($center, $zoom, $type, $placemarks = '', $editor = '', $lang = 'ru-RU', $container = 'ubmap') {
    global $ubillingConfig;
    $mapsCfg = $ubillingConfig->getYmaps();
    $result = '';
    $tileLayerCustoms = '';
    if (empty($center)) {
        //autolocator here
        $mapCenter = 'map.locate({setView: true, maxZoom: ' . $zoom . '});';
        //error notice if autolocation failed
        $mapCenter .= 'function onLocationError(e) {
                        alert(e.message);
                       }
                       map.on(\'locationerror\', onLocationError)';
    } else {
        //explicit map center
        $mapCenter = 'map.setView([' . $center . '], ' . $zoom . ');';
    }

    //default OSM tile layer
    $tileLayer = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

    //custom tile layer
    if (isset($mapsCfg['LEAFLET_TILE_LAYER'])) {
        if ($mapsCfg['LEAFLET_TILE_LAYER']) {
            $tileLayer = $mapsCfg['LEAFLET_TILE_LAYER'];
            //Visicom custom options
            if (ispos($tileLayer, 'visicom')) {
                $tileLayerCustoms = "subdomains: '123',
                tms: true";
            }
            
            //google satellite
            if (ispos($tileLayer, 'google.com')) {
                $tileLayerCustoms="subdomains:['mt0','mt1','mt2','mt3']";
            }
        }
    }



    $result .= '<link rel="stylesheet" href="modules/jsc/leaflet/leaflet.css"/>';
    $result .= wf_tag('script', false, '', 'src="modules/jsc/leaflet/leaflet.js"');
    $result .= wf_tag('script', true);
    $result .= wf_tag('script', false, '', 'type = "text/javascript"');

    $result .= '
	var map = L.map(\'' . $container . '\');
        ' . $mapCenter . '
	L.tileLayer(\'' . $tileLayer . '\', {
		maxZoom: 18,
		attribution: \'\',
		id: \'mapbox.streets\',
                ' . $tileLayerCustoms . '
	}).addTo(map);

	' . $placemarks . '
        ' . $editor . '
';
    $result .= wf_tag('script', true);
    return($result);
}

/**
 * Return generic editor code
 * 
 * @param string $name
 * @param string $title
 * @param string $data
 * 
 * @return string
 */
function generic_MapEditor($name, $title = '', $data = '') {

    $data = str_replace("'", '`', $data);
    $data = str_replace("\n", '', $data);
    $data = str_replace('"', '\"', $data);
    $content = '<form action=\"\" method=\"POST\"><input type=\"hidden\" name=' . $name . ' value=\'"+e.latlng.lat+\', \'+e.latlng.lng+"\'>' . $data . '</form>';


    //$content = str_replace('"', '\"', $content);
    $windowCode = '<b>' . $title . '</b><br>' . $content;
    $result = 'var popup = L.popup();

                function onMapClick(e) {
                        popup
                                .setLatLng(e.latlng)
                                .setContent("' . $windowCode . '<br>" + e.latlng.lat + ", " + e.latlng.lng)
                                .openOn(map);
                }

	map.on(\'click\', onMapClick);';

    return ($result);
}

/**
 * Returns JS code to draw line within two points
 * 
 * @param string $coord1
 * @param string $coord2
 * @param string $color
 * @param string $hint
 * 
 * @return string
 */
function generic_MapAddLine($coord1, $coord2, $color = '', $hint = '', $width = '') {
    $lineId = wf_InputId();
    $color = (!empty($color)) ? $color : '#000000';
    $width = (!empty($color)) ? $width + 1 : '1';

    $result = '';
    $result .= '
        var pointA = new L.LatLng(' . $coord1 . ');
        var pointB = new L.LatLng(' . $coord2 . ');
        var pointList = [pointA, pointB];

        var polyline_' . $lineId . ' = new L.Polyline(pointList, {
            color: \'' . $color . '\',
            weight: ' . $width . ',
            opacity: 0.8,
            smoothFactor: 1
        });
        polyline_' . $lineId . '.addTo(map);
        ';
    if (!empty($hint)) {
        $hint = str_replace('"', '\"', $hint);
        $result .= 'polyline_' . $lineId . '.bindTooltip("' . $hint . '", { sticky: true});';
    }
    return ($result);
}

?>