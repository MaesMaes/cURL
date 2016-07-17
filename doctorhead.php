<?php

    // Засекаем время
    //$start_time = microtime(1);

    include_once 'curl.php';
    require_once 'simple_html_dom.php';

    $post = array(
        'auth_key'=>'880ea6a14ea49e853634fbdc5015a024',
        'referer'=>'http://forum.doctorhead.ru/index.php?',
        'ips_username'=>'Maes',
        'ips_password'=>'f35a3655f2553ff',
    );

    // $c = curl::app( 'http://forum.doctorhead.ru/index.php' )->redirect(1);
    // $data =  $c->request( "?app=core&module=global&section=login" );
    // $data =  $c->post( $post )->request( "/?app=core&module=global&section=login&do=process" );

    $c = curl::app( 'http://forum.doctorhead.ru/index.php' )->cookie();
    $data =  $c->request( "/" );
    print_r( $data['headers'] );


    //echo microtime(1) - $start_time;
