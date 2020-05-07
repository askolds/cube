<?php


namespace App\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class RssController extends AbstractController
{

    const RSS_FEED = 'http://www.tvnet.lv/rss/';
    const RSS_FEED_LIMIT = 5;

    /**
     * @Route("/rss", name="show_rss")
     */
    public function showRss(): Response
    {
        $rss = simplexml_load_string(file_get_contents(self::RSS_FEED));

        $feedCount = 0;
        $feed = [];
        foreach ($rss->channel->item as $item) {

            if ($feedCount >= self::RSS_FEED_LIMIT) {
                break;
            }
            $d = new DateTime($item->pubDate);
            $item->pubDate = $d->format('d/m/Y H:i:s');

            $item->image = $item->enclosure['url'];

            $feed[] = $item;
            $feedCount++;
        }

        return $this->render('rss/main.html.twig', ['feed' => $feed, 'user' => $this->getUser()]);

    }

}