<?php

namespace App\Api\Integrations\DevProm;

use App\Api\Integrations\GuzzleHelper;
use GuzzleHttp\Client;

/**
 * Класс-интеграция с CRM DevProm.
 * Создается клиент интеграции(объект), на вход принимается конфиг интеграции из данных партнёра (url, token).
 * Class DevPromApi
 * @package App\Api\Integrations\DevProm
 */
class DevPromApi{

    /**
     * Объект Guzzl-овского клиента для обращения на апишку CRM DevProm.
     * @var Client
     */
    protected $client;

    /**
     * Заголовки.
     * @var array
     */
    protected $headers;


    /**
     * Формируем объект клиента в соответствие с пришедшей интеграцией
     * DevPromApi constructor.
     * @param $int_data
     */
    public function __construct($int_data){

        $this->client = new Client(['base_uri' => $int_data['url']]);
        $this->headers = [
            'Devprom-Auth-Key' => $int_data['token'],
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Отправка тикета о нажатии в Devprom
     * @param $associated_data
     * @return int
     */
    public function createTicket($associated_data){

        $user = $associated_data['user'];
        $button = $associated_data['button'];

        $body['Caption'] = $user['login']. ' нажал кнопку';
        $body['Description'] =
            $user['first_name'] . ' нажал кнопку ' . $button['id'] . '<br>' .
            'Контакты пользователя: ' . '<br>' .
            'Адрес: '. $user['address'] . '<br>' .
            'Телефон: ' . $user['phone'] . '<br>' .
            'Email: ' . $user['email'];
        $body = json_encode($body);

        $response = $this->client->post('',[
            'headers' => $this->headers,
            'body' => $body,

        ])->getBody()->getContents();

        $response = json_decode($response,true);
        $ticket_id = $response['Id'];

        return $ticket_id;
    }

    /**
     * Отправка повышенного приоритета в Devprom
     * @param $ticket_id
     */
    public function priorityUpdate($ticket_id){

        $body = [
            "Id" => $ticket_id,
            "Priority" => 1,
        ];
        $body = json_encode($body);

        $this->client->put('issues/'.$ticket_id,[
            'headers' => $this->headers,
            'body' => $body,
        ]);
    }

    /**
     * "Закрываем" тикет
     * @param $ticket_id
     */
    public function ticketClose($ticket_id){

        $body = [
            "Id" => $ticket_id,
            "Priority" => 7,
            "Caption" => "Пользователь отозвал заказ",
        ];
        $body = json_encode($body);

        $this->client->put('issues/'.$ticket_id,[
            'headers' => $this->headers,
            'body' => $body,
        ]);
    }





}