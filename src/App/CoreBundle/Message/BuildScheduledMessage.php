<?php

namespace App\CoreBundle\Message;

use App\Model\Build;

class BuildScheduledMessage extends AbstractMessage
{
    public function __construct(Build $build)
    {
        parent::__construct($build);
    }

    public function getRoutes()
    {
        $build = $this->getObject();

        return ['build' => [
            'show_url' => $this->generateUrl('app_core_build_show', ['id' => $build->getId()]),
            'cancel_url' => $this->generateUrl('app_core_build_cancel', ['id' => $build->getId()])
        ]];
    }
}