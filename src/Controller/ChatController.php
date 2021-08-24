<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Chat;

class ChatController extends AbstractController
{

    public function index()
    {
        $channelName = 'ninjatooken';

        $json_array = json_decode(@file_get_contents('https://api.twitch.tv/kraken/streams/'.$channelName, true), true);

        $twitchOnline = false;
        $channelTitle = '';
        $streamTitle = '';
        if ($json_array && $json_array['stream'] != NULL) {
            $channelTitle = $json_array['stream']['channel']['display_name'];
            $streamTitle = $json_array['stream']['channel']['status'];
            $twitchOnline = true;
        }

        return $this->render('chat/chat.html.twig', array(
            'twitchOnline' => $twitchOnline,
            'channelTitle' => $channelTitle,
            'streamTitle' => $streamTitle,
            'channelName' => $channelName,
            'discordServerId' => $this->getParameter('discord')['serverId'],
            'discordChannelId' => $this->getParameter('discord')['channelId']
        ));
    }
}
