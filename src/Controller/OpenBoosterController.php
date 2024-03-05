<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api/pkmImg', name:'pkmImg')]
class OpenBoosterController extends AbstractController
{

    /**
     * @throws \JsonException
     */
    public function OpenBooster()
    {

        $rand = random_int(0,151);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://pokeapi.co/api/v2/pokemon/'.$rand,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        if($data){
            $pkmName=$data['name'];
            $pkmImgUrl=$data['spites']['front_default'];
        }

    }
}