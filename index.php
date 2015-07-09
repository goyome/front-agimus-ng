<?php
    // AUTH
    $index_path = '.';
    // Load the settings from the central config file
    require_once $index_path.'/config/config.php';
    // Load the CAS lib
    require_once $phpcas_path . '/CAS.php';

    phpCAS::setDebug();

    // Initialize phpCAS
    phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

    // For production use set the CA certificate that is the issuer of the cert
    // on the CAS server and uncomment the line below
    // phpCAS::setCasServerCACert($cas_server_ca_cert_path);

    // For quick testing you can disable SSL validation of the CAS server.
    // THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
    // VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
    phpCAS::setNoCasServerValidation();

    // force CAS authentication
    phpCAS::forceAuthentication();

    // at this step, the user has been authenticated by the CAS server
    // and the user's login name can be read with phpCAS::getUser().

    // logout if desired
    if (isset($_REQUEST['logout'])) {
      //phpCAS::logout();
      if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
            $url = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $parseurl = parse_url($url);
            $dirpath = dirname($parseurl['path']);
            $service = "https://".$parseurl['host'].rtrim($dirpath, '/\\')."/";
        } else {
            $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $parseurl = parse_url($url);
            $dirpath = dirname($parseurl['path']);
            $service = "http://".$parseurl['host'].rtrim($dirpath, '/\\')."/";
        }
      phpCAS::logoutWithRedirectService($service);
    }


    /*** CHECK IF USER IS KNOWN ***/

    // charge et initialise les bibliothèques globales
    
    require_once $index_path.'/controller/controller.php';

    $casdata = phpCAS::getAttributes();
    $email = isset($casdata['mail']) ? $casdata['mail'] : "";

    $user = get_or_create_user(phpCAS::getUser(), $email);
    $dashboards = get_user_dashboards($user);

    require_once './views/views.php';

    // route la requête en interne
    //$uri = $_SERVER ['REQUEST_URI'];
    $uri=strtok($_SERVER["REQUEST_URI"],'?');

    if('/index.php'==$uri){
        redirect_to_default_dashboard_action($user);
    } elseif(preg_match(',/index.php/dashboard/(?P<id>\d+)/,', $uri, $matches)) {
        show_dashboard_action($matches['id']);
    } elseif(preg_match(',/index.php/graphe/(?P<id>\d+)/,', $uri, $matches)) {
        show_graphe_action($matches['id']);
    } else {
      header('Status: 404 Not Found');
      echo '<html><body><h1>Page Not Found</h1></body></html>';
    }
    
?>



