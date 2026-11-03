# Produman API PHP Client Library

Клиент для работы с онлайн кассой Продуман по [API](https://dev.produman.org/api)

# Содержание

* [Требования](#Требования)
* [Установка](#Установка)
* [Переход с 1.x](#переход-с-1x)
* [Проверки SDK](#проверки-sdk)
* [Порядок работы с клиентом](#Порядок-работы-с-клиентом)
* [Примеры использования основных API методов](#Примеры-использования-основных-API-методов)
  * [Инициализация клиента](#1-инициализация-клиента) 
  * [Справочник касс](#2-справочник-касс)
  * [Справочник категорий внесений и выплат](#3-справочник-категорий-внесений-и-выплат)
  * [Справочник контрагентов](#4-справочник-контрагентов)
  * [Справочник сотрудников](#5-справочник-сотрудников)
  * [Работа с операциями](#6-работа-с-операциями)
  * [Работа с заказами](#7-работа-с-заказами)
* [Примеры использования сервисных API методов](#Примеры-использования-сервисных-API-методов)
  * [Инициализация клиента сервисных запросов](#1-инициализация-клиента-сервисных-запросов) 
  * [Вебхуки](#2-вебхуки)
  * [Интеграции](#3-интеграции)
* [Примеры работы с исключениями](#примеры-работы-с-исключениями)
* [Примеры использования RateLimit](#примеры-использования-ratelimit)
* [Код товара или услуги в позиции](#код-товара-или-услуги-в-позиции)

## Требования
PHP 8.5 с расширениями json и curl, Symfony HttpClient `^8.1`, HttpClient Contracts `^3.7` и PSR-3 `^3.0`.

## Установка
### В консоли с помощью Composer

1. Установите менеджер пакетов Composer.
2. В консоли выполните команду
```bash
composer require produman-org/kassa-php
```

### В файле composer.json своего проекта
1. Добавьте строку `"produman-org/kassa-php": "^2.0"` в список зависимостей вашего проекта в файле composer.json
```
...
    "require": {
        "php": "^8.5",
        "produman-org/kassa-php": "^2.0"
...
```
2. Обновите зависимости проекта. В консоли перейдите в каталог, где лежит composer.json, и выполните команду:
```bash
composer update
```
3. В коде вашего проекта подключите автозагрузку файлов нашего клиента:
```php
require __DIR__ . '/vendor/autoload.php';
```

## Переход с 1.x

Перед установкой 2.x подготовьте PHP 8.5 и совместимые версии зависимостей. Вызовы `Builder::buildClient()` и `Builder::buildApplicationClient()`, группы методов и изменяемые модели запросов сохранены.

- SDK запрещает автоматические HTTP-перенаправления. Ответ 3xx приводит к `HttpException`; реквизиты не передаются адресу из `Location`. Для альтернативной точки API укажите её окончательный адрес через `PRODUMAN_API_URL`.
- Собственный журнал SDK содержит метаданные: метод, адрес без userinfo/query/fragment, HTTP-статус и доступные лимиты. Тела запросов/ответов, заголовки аутентификации, сырые сообщения исключений и trace не записываются. Передача самого исключения в сторонний логгер регулируется настройками этого логгера.
- Ошибка кодирования запроса, например неверный UTF-8, приводит к `JsonRequestException` до вызова HTTP-клиента. Ошибка синтаксиса или скалярный корень JSON успешного ответа — к `JsonResponseException`. При ошибке синтаксиса исходный `JsonException` сохранён в `getPrevious()`; допустимые корневые массивы продолжают обрабатываться.
- Структурированная ошибка 4xx остаётся `ApiException`. Повреждённое тело ошибки 4xx, ответ 5xx, перенаправление или сетевой сбой дают `HttpException` с исходной причиной. Методы без DTO, например удаление, допускают пустой успешный ответ.
- Для 3xx/5xx сохраняется HTTP-причина Symfony: SDK не перечитывает тело для её замены, если Symfony уже подавил ошибку чтения при создании HTTP-исключения. Непосредственно выброшенная транспортом ошибка чтения становится `getPrevious()` исключения SDK.
- Сведения `getLastStatusCode()` и `getLastXRateLimit*()` относятся к последнему вызову HTTP-адаптера. Они очищаются в начале этого вызова и сохраняют уже полученные сведения при сбое чтения тела. Ошибка кодирования до HTTP-вызова оставляет метаданные предыдущего HTTP-запроса.

Параметр `timeout` ограничивает простой соединения, а не суммарную длительность запроса. SDK не выполняет автоматические повторы: повтор кассовой операции после неизвестного результата требует проверки её состояния и контракта API.

## Проверки SDK

Для разработки используется PHPUnit 13.4. Установите зависимости на PHP 8.5 с расширениями curl, dom, mbstring, xml и xmlwriter:

```bash
composer install
composer test
composer test:integration
composer test:consumer
composer test:lowest
```

`test` запускает весь набор, включая локальный HTTP-сервер с настоящим CurlHttpClient. `test:consumer` устанавливает копию изменённого SDK в отдельный временный Composer-проект без его зависимостей разработки и проверяет публичные методы из `vendor`. `test:lowest` сначала выбирает минимальные production-версии, затем фиксирует их и добавляет PHPUnit для полного прогона. Эти две проверки используют сеть только для установки пакетов Composer, а HTTP-запросы выполняют к локальным fixtures с вымышленными реквизитами. Репозиторий приложения-потребителя не требуется.

Локальным тестам нужны `proc_open` и два свободных порта, по умолчанию 42100–42101; первый задаётся переменной `KASSA_TEST_PORT_BASE`. Серверы и временные проекты удаляются после проверки. GitHub Actions проверяет линию 8.1, последние разрешённые зависимости и нижние production-границы; предупреждения и пропуски обязательных тестов делают прогон неуспешным.

## Порядок работы с клиентом

1. Для основных API запросов ([справочники](https://dev.produman.org/api#tag/Spravochniki), [операции](https://dev.produman.org/api#tag/Operacii) или [заказы](https://dev.produman.org/api#tag/Zakazy)) создайте экземпляр объекта клиента при помощи команды `buildClient`, задайте клиентский токен, идентификатор приложения и секретный ключ (их можно получить в личном кабинете Продуман).
```php
use ProdumanApi\Builder;

$client = Builder::buildClient(
    'someClientToken',
    'someAppId',
    'someAppSecret'
);
```

2. Для сервисных API запросов ([вебхуки](https://dev.produman.org/api#tag/Vebhuki) и [интеграции](https://dev.produman.org/api#tag/Integracii)) создайте экземпляр объекта клиента при помощи команды `buildApplicationClient` (аналогично, как в примере выше, но без указания `clientToken`).

3. Дополнительно, при создании клиента, можно скорректировать параметр `timeout` для curl запросов, использовать логгер `Psr\Log\LoggerInterface` в параметре `logger`, а так же указать передачу заголовка `Accept-Language` в параметре `language`.

4. Вызовите нужный метод API в объекте `$client`.

5. Для каждого API запроса клиент содержит в себе соответствующий метод.
Методы, если требуется, в качестве параметра принимают объекты классов запросов `ProdumanApi\Request\...`.
В качестве ответа, методы возвращают объекты классов ответов `ProdumanApi\Response\...`.

6. Дополнительно имеется поддержка исключений `ProdumanApi\Exception\...`. Например, исключение `ProdumanApi\Exception\ApiException` используется для вывода данных, описанных в разделе [обработка ошибок API](https://dev.produman.org/api#section/Obrabotka-oshibok).

7. В API Продуман имеются ограничения на количество и частоту запросов [RateLimit](https://dev.produman.org/api#section/RateLimit). Для получения информации о текущем лимите в объекте клиента имеются соответствующие методы.
## Примеры использования основных API методов

### 1. Инициализация клиента
```php
use ProdumanApi\Builder;
use ProdumanApi\Client\Client;
use Example\Logger;

/** @var Client $client */
$client = Builder::buildClient(
    'someClientToken',
    'someAppId',
    'someAppSecret',
    30,
    new Logger(),
    'ru'
);
```

### 2. Справочник касс
```php
use ProdumanApi\Request\Cashboxes\ListRequest;
use ProdumanApi\Response\Cashboxes\ListDto;
use ProdumanApi\Response\Cashboxes\CashboxDto;

// касса по id
/** @var CashboxDto $response */
$response = $client->cashboxes->get('1ee57a8d-f1b4-6764-9ec7-a537555944ab');

// список касс без фильтра
/** @var ListDto $response */
$response = $client->cashboxes->list();

// список касс по фильтру
$request = new ListRequest();
$request->limit = 2;
$request->status = 'ACTIVE';
$request->cursor = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
/** @var ListDto $response */
$response = $client->cashboxes->list($request);
```

### 3. Справочник категорий внесений и выплат
```php
use ProdumanApi\Request\CashMovementCategories\ListRequest;
use ProdumanApi\Response\CashMovementCategories\ListDto;

// список категорий внесений и выплат без фильтра
/** @var ListDto $response */
$response = $client->cashMovementCategories->list();

// список категорий внесений и выплат по фильтру
$request = new ListRequest();
$request->limit = 2;
$request->type = 'IN';
$request->cursor = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
/** @var ListDto $response */
$response = $client->cashMovementCategories->list($request);
```

### 4. Справочник контрагентов
```php
use ProdumanApi\Request\Counterparties\ListRequest;
use ProdumanApi\Response\Counterparties\ListDto;

// список контрагентов без фильтра
/** @var ListDto $response */
$response = $client->counterparties->list();

// список контрагентов по фильтру 
$request = new ListRequest();
$request->limit = 2;
$request->search = '123456789';
$request->cursor = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
/** @var ListDto $response */
$response = $client->counterparties->list($request);
```

### 5. Справочник сотрудников
```php
use ProdumanApi\Request\Employees\ListRequest;
use ProdumanApi\Response\Employees\ListDto;

// список сотрудников без фильтра
/** @var ListDto $response */
$response = $client->employees->list();

// список сотрудников по фильтру
$request = new ListRequest();
$request->limit = 2;
$request->status = 'ACTIVE';
$request->cursor = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
/** @var ListDto $response */
$response = $client->employees->list($request);
```

### 6. Работа с операциями
```php
use ProdumanApi\Request\Operations\CashEditRequest;
use ProdumanApi\Request\Operations\CreateCashIn;
use ProdumanApi\Request\Operations\CreateKktInfo;
use ProdumanApi\Request\Operations\ListRequest;
use ProdumanApi\Request\Operations\CreateSell;
use ProdumanApi\Request\Operations\CreateShiftOpen;
use ProdumanApi\Request\Operations\CreateShiftClose;
use ProdumanApi\Request\Operations\CreateXReport;
use ProdumanApi\Request\Operations\Model\ClientModel;
use ProdumanApi\Request\Operations\Model\DetailsCashModel;
use ProdumanApi\Request\Operations\Model\DetailsSellModel;
use ProdumanApi\Request\Operations\Model\DetailsShiftCloseModel;
use ProdumanApi\Request\Orders\Model\AgentSchemeModel;
use ProdumanApi\Request\Orders\Model\PositionModel;
use ProdumanApi\Request\Orders\Model\SupplierModel;
use ProdumanApi\Response\Operations\ListDto;
use ProdumanApi\Response\Operations\OperationDto;

// операция по id
/** @var OperationDto $response */
$response = $client->operations->get('1ee57a8d-f1b4-6764-9ec7-a537555944ab');

// список операций без фильтра
/** @var ListDto $response */
$response = $client->operations->list();

// список операций по фильтру
$request = new ListRequest();
$request->limit = 2;
$request->status = 'COMPLETE';
$request->operationType = 'SELL';
$request->createdAtFrom = new \DateTime('- 1 year');
$request->createdAtTo = new \DateTime('now');
$request->operationIds = [
    '1ee57a8d-f1b4-6764-9ec7-a537555944ab',
];
$request->cursor = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
/** @var ListDto $response */
$response = $client->operations->list($request);

// повтор операции по id
/** @var OperationDto $response */
$response = $client->operations->retry('1ee57a8d-f1b4-6764-9ec7-a537555944ab');

// редактирование операции с наличными
$request = new CashEditRequest();
$request->cashMovementCategoryId = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
$request->comment = 'Тестовый комментарий';
 /** @var OperationDto $response */
$response = $client->operations->cashMovementEdit('1ee57a8d-f1b4-6764-9ec7-a537555944ab', $request);

//создание операции X-отчет
$request = new CreateXReport();
$request->cashboxId = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
/** @var OperationDto $response */
$response = $client->operations->create($request);

// создание операции открытия смены
$request = new CreateShiftOpen();
$request->cashboxId = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
/** @var OperationDto $response */
$response = $client->operations->create($request);

// создание операции закрытия смены
$details = new DetailsShiftCloseModel();
$details->onlyCashbox = true;
$request = new CreateShiftClose();
$request->cashboxId = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
$request->details = $details;
/** @var OperationDto $response */
$response = $client->operations->create($request);
    
// создание операции информации о ККТ
$request = new CreateKktInfo();
$request->cashboxId = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
/** @var OperationDto $response */
$response = $client->operations->create($request);
    
// создание операции внесения наличных
$details = new DetailsCashModel();
$details->cashMovementCategoryId = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
$details->comment = 'Тестовый комментарий';
$details->amount = 10.00;
$request = new CreateCashIn();
$request->cashboxId = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
$request->details = $details;
/** @var OperationDto $response */
$response = $client->operations->create($request);

// создание операции прихода без заказа с действием формирования чека (actionType="PREPARE")
$operationClient = new ClientModel();
$operationClient->name = 'Тестовый клиент';
$operationClient->inn = '485466710568';
$supplier = new SupplierModel();
$supplier->name = 'Тестовый поставщик';
$supplier->inn = '485466710568';
$supplier->phone = '+79999999999';
$agentScheme = new AgentSchemeModel();
$agentScheme->agentSign = 'ANOTHER_AGENT';
$agentScheme->supplier = $supplier;
$position = new PositionModel();
$position->name = 'Тестовая позиция';
$position->quantity = 2;
$position->price = 10.00;
$position->paymentVat = 'WITHOUT';
$position->paymentObject = 'PRODUCT';
$position->paymentMethod = 'FULL_PAYMENT';
$position->excisable = false;
$position->agentScheme = $agentScheme;
$position->marks = [
  '010641944077751221s40h&mLFSVODA93TEST',
];
$details = new DetailsSellModel();
$details->actionType = 'PREPARE';
$details->taxationSystem = 'OSN';
$details->client = $operationClient;
$details->positions = [$position];
$request = new CreateSell();
$request->cashboxId = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
$request->details = $details;
/** @var OperationDto $response */
$response = $client->operations->create($request);

// создание операции прихода без заказа с действием регистрации чека (actionType="EXECUTE")
$paymentSolution = new PaymentSolutionModel();
$paymentSolution->id = 1;
$paymentSolution->amount = 20.0;
$operationClient = new ClientModel();
$operationClient->name = 'Тестовый клиент';
$operationClient->inn = '485466710568';
$supplier = new SupplierModel();
$supplier->name = 'Тестовый поставщик';
$supplier->inn = '485466710568';
$supplier->phone = '+79999999999';
$agentScheme = new AgentSchemeModel();
$agentScheme->agentSign = 'ANOTHER_AGENT';
$agentScheme->supplier = $supplier;
$position = new PositionModel();
$position->name = 'Тестовая позиция';
$position->quantity = 2;
$position->price = 10.00;
$position->paymentVat = 'WITHOUT';
$position->paymentObject = 'PRODUCT';
$position->paymentMethod = 'FULL_PAYMENT';
$position->excisable = false;
$position->agentScheme = $agentScheme;
$position->marks = [
  '010641944077751221s40h&mLFSVODA93TEST',
];
$details = new DetailsSellModel();
$details->actionType = 'EXECUTE';
$details->taxationSystem = 'OSN';
$details->client = $operationClient;
$details->receiptContact = 'test@test.ru';
$details->print = true;
$details->internetPayment = true;
$details->settlementPlace = 'Россия, Москва';
$details->documentNumber = '12345';
$details->paymentSolutions = [$paymentSolution];
$details->positions = [$position];
$request = new CreateSell();
$request->cashboxId = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
$request->details = $details;
/** @var OperationDto $response */
$response = $client->operations->create($request);

// остальные типы операций создаются аналогично
```

### 7. Работа с заказами
```php
use ProdumanApi\Request\Orders\CreateRequest;
use ProdumanApi\Request\Orders\ListRequest;
use ProdumanApi\Request\Orders\Model\AgentSchemeModel;
use ProdumanApi\Request\Orders\Model\ClientModel;
use ProdumanApi\Request\Orders\Model\DeliveryModel;
use ProdumanApi\Request\Orders\Model\PaymentAgentModel;
use ProdumanApi\Request\Orders\Model\PositionModel;
use ProdumanApi\Request\Orders\Model\ReceivePaymentsOperatorModel;
use ProdumanApi\Request\Orders\Model\SupplierModel;
use ProdumanApi\Request\Orders\Model\TransferOperatorModel;
use ProdumanApi\Response\Orders\ListDto;
use ProdumanApi\Response\Orders\OrderDto;

// заказ по id
/** @var OrderDto $response */
$response = $client->orders->get('1ee57a8d-f1b4-6764-9ec7-a537555944ab');

// список заказов без фильтра
/** @var ListDto $response */
$response = $client->orders->list();

// список заказов по фильтру
$request = new ListRequest();
$request->limit = 2;
$request->totalAmountFrom = 100;
$request->totalAmountTo = 100000;
$request->createdAtFrom = new \DateTime('- 1 year');
$request->createdAtTo = new \DateTime('now');
$request->cursor = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
/** @var ListDto $response */
$response = $client->orders->list($request);

// создание заказа
$orderClient = new ClientModel();
$orderClient->name = 'Тестовый клиент';
$orderClient->inn = '485466710568';
$orderClient->email = 'test@test.ru';
$orderClient->phone = '+79999999999';
$delivery = new DeliveryModel();
$delivery->courierId = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
$delivery->address = 'Россия, Москва';
$delivery->dateFrom = new \DateTime('- 1 min');
$delivery->dateTo = new \DateTime('+ 1 min');
$delivery->status = 'MOVED';
$delivery->comment = 'Тестовый комментарий';
$delivery->prepaid = false;
$paymentAgent = new PaymentAgentModel();
$paymentAgent->phone = '+79999999999';
$paymentAgent->operation = 'Пополнение счета';
$transferOperator = new TransferOperatorModel();
$transferOperator->name = 'ПАО КИВИ БАНК';
$transferOperator->inn = '485466710568';
$transferOperator->phone = '+79999999999';
$transferOperator->address = 'Россия, Москва';
$supplier = new SupplierModel();
$supplier->name = 'Тестовый поставщик';
$supplier->inn = '485466710568';
$supplier->phone = '+79999999999';
$agentScheme = new AgentSchemeModel();
$agentScheme->agentSign = 'BANK_PAYING_AGENT';
$agentScheme->paymentAgent = $paymentAgent;
$agentScheme->transferOperator = $transferOperator;
$agentScheme->supplier = $supplier;
$position = new PositionModel();
$position->name = 'Тестовая позиция';
$position->quantity = 2;
$position->price = 10.00;
$position->paymentVat = 'WITHOUT';
$position->paymentObject = 'PRODUCT';
$position->paymentMethod = 'FULL_PAYMENT';
$position->excisable = false;
$position->agentScheme = $agentScheme;
$position->marks = [
  '010641944077751221s40h&mLFSVODA93TEST',
];
$request = new CreateRequest();
$request->createdById = '1ee57a8d-f1b4-6764-9ec7-a537555944ab';
$request->externalId = '1234567890';
$request->taxationSystem = 'OSN';
$request->client = $orderClient;
$request->delivery = $delivery;
$request->positions[] = $position;
/** @var OrderDto $response */
$response = $client->orders->create($request);

// обновление заказа по id (запрос с таким же объектом $request, что и при создании)
/** @var OrderDto $response */
$response = $client->orders->update('1ee57a8d-f1b4-6764-9ec7-a537555944ab', $request);

// удаление заказа по id
$client->orders->delete('1ee57a8d-f1b4-6764-9ec7-a537555944ab');
```

## Код товара или услуги в позиции

Предчек в именах SDK называется заказом. Общая модель
`ProdumanApi\Request\Orders\Model\PositionModel` содержит необязательный
`itemCode`: код товара или услуги из внешней учётной системы для
сопоставления с карточкой на кассе.

```php
use ProdumanApi\Request\Orders\Model\PositionModel;

$position = new PositionModel();
$position->name = 'Товар';
$position->itemCode = '000123';
$position->quantity = 1;
$position->price = 100;
$position->paymentObject = 'PRODUCT';
```

Эта позиция используется в `CreateRequest::$positions` при создании
и замене предчека и в `DetailsSellModel::$positions` или
`DetailsBuyModel::$positions` при прямой операции. При операции по
`orderId` код берётся из сохранённого предчека.

SDK передаёт строку без преобразований, сохраняя ведущие нули, регистр
и пробелы вокруг непустого значения. Значение по умолчанию — `null`;
при отсутствии поля в ответе оно также остаётся `null`. Код читается
через `OrderDto::$positions` и в элементах списка предчеков.

Сервер принимает строку до 255 символов; пустую строку, только пробелы
и неверный тип отклоняет. Для операции на Эвоторе действует предел
25 символов. Предчек с более длинным кодом сохранить можно, но операция
на Эвоторе будет отклонена. При полной замене предчека без кода у новой
позиции кода нет. Возможности драйверов и ответы прямых операций не меняются.

## Примеры использования сервисных API методов

### 1. Инициализация клиента сервисных запросов
```php
use ProdumanApi\Builder;
use ProdumanApi\Client\ApplicationClient;
use Example\Logger;

/** @var ApplicationClient $client */
$client = Builder::buildApplicationClient(
    'someAppId',
    'someAppSecret',
    30,
    new Logger(),
    'ru'
);
```

### 2. Вебхуки
```php
use ProdumanApi\Request\Webhooks\EndpointCreateRequest;
use ProdumanApi\Request\Webhooks\EndpointListRequest;
use ProdumanApi\Request\Webhooks\EndpointTestRequest;
use ProdumanApi\Response\Webhooks\EndpointDto;
use ProdumanApi\Response\Webhooks\EndpointListDto;

// эндпоинт по id
/** @var EndpointDto $response */
$response = $client->webhooks->endpointGet('1ee30707-89ed-696a-8501-a1e907a309b2');

// список эндпоинтов без фильтра
/** @var EndpointListDto $response */
$response = $client->webhooks->endpointList();

// список эндпоинтов по фильтру
$request = new EndpointListRequest();
$request->limit = 2;
$request->status = 'ACTIVE';
/** @var EndpointListDto $response */
$response = $client->webhooks->endpointList($request);

// создание эндпоинта
$request = new EndpointCreateRequest();
$request->status = 'INACTIVE';
$request->url = 'https://rra.ru';
$request->authType = 'BEARER_TOKEN';
$request->secret = '1234567890';
$request->events = ['OPERATION_FAILED'];
/** @var EndpointDto $response */
$response = $client->webhooks->endpointCreate($request);

// обновление эндпоинта по id (запрос с таким же объектом $request, что и при создании)
/** @var EndpointDto $response */
$response = $client->webhooks->endpointUpdate('1eeed9f2-a669-6fde-ad9f-1982a8a5337a', $request);

// удаление эндпоинта
$client->webhooks->endpointDelete('1ee921aa-9827-69d6-b16d-9f7549035bae');

// тестовая отправка вебхука с заданным событием
$request = new EndpointTestRequest();
$request->event = 'INTEGRATION_REQUEST_COMPLETED';
$request->skipVerify = true;
$client->webhooks->endpointTest($request);
```

### 3. Интеграции
```php
use ProdumanApi\Request\Integrations\CreateConnectRequest;
use ProdumanApi\Response\Integrations\IntegrationConnectDto;
use ProdumanApi\Response\Integrations\IntegrationDto;

// интеграция по id
/** @var IntegrationDto $response */
$response = $client->integrations->get('1ee879f7-0eff-6092-9dc9-5d26875ad7b4');

// деактивация интеграции по id
$client->integrations->deactivate('1ee879f7-0eff-6092-9dc9-5d26875ad7b4');

// запрос на создание новой интеграции
$request = new CreateConnectRequest();
$request->email = 'test@test.ru';
 /** @var IntegrationConnectDto $response */
$response = $client->integrations->create($request);
```

## Примеры работы с исключениями

```php
use ProdumanApi\Builder;
use ProdumanApi\Response\Cashboxes\CashboxDto;
use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Exception\JsonRequestException;
use ProdumanApi\Exception\JsonResponseException;

$client = Builder::buildClient(
    'someClientToken',
    'someAppId',
    'someAppSecret'
);

try {
  // касса по id
  /** @var CashboxDto $response */
  $response = $client->cashboxes->get('1ee879f7-0eff-6092-9dc9-5d26875ad7b4');
} catch (ApiException $e) {
    var_dump('API error: ' . $e->getApiMessage()
        . '|' . $e->getApiCode() . '|' . print_r($e->getApiDetails(), true) . '|' . $e->getStatusCode());
} catch (HttpException $e) {
    var_dump('HTTP error: ' . $e->getMessage());
} catch (JsonRequestException $e) {
    var_dump('JSON request error: ' . $e->getMessage());
} catch (JsonResponseException $e) {
    var_dump('JSON response error: ' . $e->getMessage());
} catch (\Exception $e) {
    var_dump('Other error: ' . get_class($e) . ': ' . $e->getMessage());
}
```

## Примеры использования RateLimit
```php
use ProdumanApi\Builder;

$client = Builder::buildClient(
    'someClientToken',
    'someAppId',
    'someAppSecret'
);
  
// общий лимит
var_dump($client->getLastXRateLimitLimit());

// текущий остаток квоты
var_dump($client->getLastXRateLimitRemaining());

// HTTP-код ответа последнего запроса
var_dump($client->getLastStatusCode());
```


### [Наверх](#Produman-API-PHP-Client-Library)
