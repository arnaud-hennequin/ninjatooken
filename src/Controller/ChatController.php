<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChatController extends AbstractController
{
    #[Route('/chat/', name: 'ninja_tooken_chat_homepage')]
    public function index(): Response
    {
        $channelName = 'ninjatooken';

        $content = file_get_contents('https://api.twitch.tv/kraken/streams/'.$channelName, true);
        if ($content === false) {
            $content = '';
        }
        $json_array = json_decode($content, true);

        $twitchOnline = false;
        $channelTitle = '';
        $streamTitle = '';
        if ($json_array && null != $json_array['stream']) {
            $channelTitle = $json_array['stream']['channel']['display_name'];
            $streamTitle = $json_array['stream']['channel']['status'];
            $twitchOnline = true;
        }

        return $this->render('chat/chat.html.twig', [
            'twitchOnline' => $twitchOnline,
            'channelTitle' => $channelTitle,
            'streamTitle' => $streamTitle,
            'channelName' => $channelName,
            'discordServerId' => $this->getParameter('discord')['serverId'],
            'discordChannelId' => $this->getParameter('discord')['channelId'],
        ]);
    }
}
