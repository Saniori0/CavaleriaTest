<?php

declare(strict_types=1);


use Saniori\Cavaleria\Api;
use Saniori\Cavaleria\Client;

require "../vendor/autoload.php";

$config = [
    "host" => "https://api.moysklad.ru/api/remap/1.2",
    "account" => [
        "login" => "",
        "password" => ""
    ],
    "productID" => "172e0414-bdc5-11ee-0a80-17650026015e", // ID Товара "Тестовое задание"
    "storeID" => "4a930c5b-b899-11e7-7a31-d0fd00276a71"
];

$Api = new Api($config["host"]);
$Client = new Client($Api);

if (!$Client->auth($config["account"]["login"], $config["account"]["password"])) {

    exit("Не верный пароль");

}

print("Выполнен вход в аккаунт по почте {$config["account"]["login"]}" . PHP_EOL);

$CounterPartyName = "Rodion";
$CounterParty = $Client->createCounterPartyByName($CounterPartyName);

if (!$CounterParty) {

    exit("Произошла ошибка при создании контрагента");

}

print("Контрагент $CounterPartyName успешно создан" . PHP_EOL);

$CounterPartyMetadata = $CounterParty["meta"];

$Organization = $Client->getOrganization();

if (!$Organization) {

    exit("Произошла ошибка при получении информации о Юр. лице");

}

$OrganizationMetadata = $Organization["rows"][0]["meta"];

$CustomerOrder = $Client->newCustomerOrder($OrganizationMetadata, $CounterPartyMetadata);

if (!$CustomerOrder) {

    exit("Произошла ошибка при создании заказа покупателя");

}

$Product = $Client->getProduct($config["productID"]);

if(!$Product){

    exit("Товара {$config["productID"]} не существует");

}

print("Заказ успешно создан" . PHP_EOL);

$OrderPosition = $Client->addPositionForCustomerOrder([
    "href" => "https://api.moysklad.ru/api/remap/1.2/entity/product/{$config["productID"]}",
    "type" => "product",
    "mediaType" => "application/json"
], $CustomerOrder["id"], [
    "quantity" => 3,
    "discount" => 7,
    "price" => $Product["salePrices"][0]["value"], // Получение первой цены, цены продажи
]);

if (!$OrderPosition) {

    exit("Произошла ошибка при добавлении позиции");

}

print("Позиция успешно добавлена " . PHP_EOL);

$DemandPosition = $Client->newDemand($OrganizationMetadata, $CounterPartyMetadata, [
    "href" => "https://api.moysklad.ru/api/remap/1.2/entity/store/{$config["storeID"]}",
    "type" => "store",
    "mediaType" => "application/json"
]);

if (!$OrderPosition) {

    exit("Произошла ошибка при добавлении отгрузки");

}

print("Отгрузка успешно добавлена " . PHP_EOL);
