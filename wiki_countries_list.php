<?php

    // Засекаем время
    //$start_time = microtime(1);

    include_once 'lib/curl.php';
    require_once 'lib/simple_html_dom.php';

    $c = curl::app( 'https://en.wikipedia.org' )
        ->ssl(1)
        ->redirect(1)
        ->headers(1)
        ->cookie('wiki')
        ->referer('google.com')
        ->config_save('wiki')
        ;

    $data =  $c->request( 'wiki/List_of_sovereign_states' );

    $dom = str_get_html( $data['html'] );
    $flags = $dom->find( '.flagicon' );

    // Страны
    $contries = array();

    foreach ( $flags as $span )
    {
        $b = $span->parent();

        if( $b->tag != 'b' )
            continue;

        // Получить 1 объект
        $a = $b->find('a', 0);
        $contries[$a->href] = $a->plaintext;
    }

    file_put_contents( 'res/all.json', json_encode( $contries ) );

    // echo $done;
    //
    echo '<pre>';
    print_r( $contries );
    echo '</pre>';
    //

    //echo microtime(1) - $start_time;
