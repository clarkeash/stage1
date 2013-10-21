<?php

namespace App\AdminBundle\Controller;

class WebsocketController extends Controller
{
    public function routingAction()
    {
        $redis = $this->container->get('app_core.redis');
        $tables_raw = $redis->keys('channel:routing:*');

        $redis->multi();

        foreach ($tables_raw as $table) {
            $redis->smembers($table);
        }

        $results = $redis->exec();

        $tables = [];

        foreach ($results as $i => $result) {
            $tables[$tables_raw[$i]] = $result;
        }

        return $this->render('AppAdminBundle:Websocket:routing.html.twig', [
            'tables' => $tables,
        ]);
    }
}