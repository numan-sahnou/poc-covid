<?php

namespace App\Controller;

use App\Services\API;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilder;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class HomeController extends AbstractController
{


    /**
     * @Route("/", name="home")
     * @param API $api
     * @param ChartBuilderInterface $chartBuilder
     * @return Response
     * @throws \Exception
     */
    public function index(API $api, ChartBuilderInterface $chartBuilder): Response
    {

        //array pour chartjs
        $label = [];
        $hospitalisation = [];
        $reanimation = [];
        $casConfirmes = [];
        $gueris = [];

        for($i = 1; $i < 8; $i++){
            $date = New \DateTime('- '.$i . ' day');
            $data = $api->getDateData($date->format('Y-m-d'));

            foreach ($data['allFranceDataByDate'] as $d){
                if($d['nom'] === 'France'){
                    $label[] =  $d['date'];
                    $hospitalisation[] = $d['nouvellesHospitalisations'];
                    $reanimation[] = $d['nouvellesReanimations'];
                    $casConfirmes[] = $d['casConfirmes'];
                    $gueris[] = $d['gueris'];

                    break;
                }
            }
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => array_reverse($label),
            'datasets' =>[
                [
                    'label' => 'Nouvelle Hospitalisations',
                    'borderColor' => 'rgb(130, 104, 142)',
                    'data' => array_reverse($hospitalisation),
                ],
                [
                    'label' => 'Nouvelle réanimations',
                    'borderColor' => 'rgb(34, 46, 42)',
                    'data' => array_reverse($reanimation),
                ],

            ]
        ]);

        $chart->setOptions([
            'title' => [
                'display' => true,
                'text' => 'Hospitalisations et Réanimations en France du '. min($label) .' au '. max($label)
            ]
        ]);


        $chartCas = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chartCas->setData([
            'labels' => array_reverse($label),
            'datasets' =>[
                [
                    'label' => 'Cas confirmés (total)',
                    'backgroundColor' => 'rgb(164,182,193)',
                    'borderColor' => 'rgb(12,33,65)',
                    'borderWidth' => '1',
                    'data' => array_reverse($casConfirmes),
                ],
                [
                    'label' => 'Gueris (total)',
                    'backgroundColor' => 'rgb(255,221,221)',
                    'borderColor' => 'rgb(255,116,116)',
                    'borderWidth' => '1',
                    'data' => array_reverse($gueris),
                ],

            ]
        ]);

        $chartCas->setOptions([
            'title' => [
                'display' => true,
                'text' => 'Evolution guérison et cas confirmés du '. min($label) .' au '. max($label)
            ]
        ]);


        return $this->render('home/index.html.twig', [
            'data' => $api->getFranceData(),
            'chart' => $chart,
            'chartCas' => $chartCas,
        ]);
    }
}
