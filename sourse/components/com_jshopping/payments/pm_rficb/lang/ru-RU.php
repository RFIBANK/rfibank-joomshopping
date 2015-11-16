<?php
//защита от прямого доступа
defined('_JEXEC') or die();

define('ADMIN_CFG_RFICB_SECRET_KEY', 'Секретный ключ');
define('ADMIN_CFG_RFICB_SECRET_KEY_DESCRIPTION', 'Секретный ключ можно посмотреть в личном кабинете Rficb "Инструменты / Сервисы" на странице редактирования сервиса или на странице "Инструменты / API ключи".');
define('ADMIN_CFG_RFICB_KEY', 'Ключ платежа');
define('ADMIN_CFG_RFICB_KEY_DESCRIPTION', 'Ключ платежа, генерируется с личном кабинете Rficb, используется для подписи платежа.');

define('RFICB_REDIRECT_PENDING_STATUS_ERROR', '<p>Ошибка! Статус заказа по-умолчанию должен совпадать со "статусом заказа для незавершенных транзакций" в настройках модуля оплаты.</p>');
define('RFICB_REDIRECT_TO_PAYMENT_PAGE', '<p>Спасибо за заказ! Сейчас Вы будете перенаправлены на страницу оплаты.</p>');

define('RFICB_ERROR_POST_FIELDS_REQUIRED', 'Для данного POST запроса должны быть переданы параметры');
define('RFICB_ERROR_SIGN_VALIDATION_FAILED', 'Ошибка проверки подписи запроса об оплате');
define('RFICB_ERROR_ORDER_ID_ERROR', 'Проблема с получением id заказа из объекта xml');
define('RFICB_ERROR_ORDER_INFORMATION_FAILED', 'Не удалось получить информацию о заказе по id заказа из запроса');
define('RFICB_ERROR_AMOUNT_CHECK_FAILED', 'Ошибка проверки суммы: сумма в XML запросе меньше суммы заказа');
define('RFICB_ERROR_CURRENCY_CHECK_FAILED', 'Ошибка проверки валюты: валюта в XML запросе не соответствует валюте заказа');
define('RFICB_ERROR_ORDER_PAID_ALREADY', 'Данный заказ уже был оплачен ранее');
define('RFICB_ERROR_UNKNOWN_STATUS_FIELD_VALUE', 'Неизвестное значение поля status в XML запросе');
define('RFICB_UNKNOWN_ERROR', 'Во время обработки запроса произошла неизвестная ошибка');

define('RFICB_SUCCESS_URL_REQUEST_PASSED', 'Запрос на Success Url обработан успешно');
define('RFICB_SUCCESS_URL_ORDER_ALREADY_PAID', 'Заказ уже оплачен (уже был Result)');
define('RFICB_SUCCESS_URL_ORDER_STATUS_FAILED', 'Статус заказа по-умолчанию не соответствует статусу незавершённых транзакций');
define('RFICB_RESULT_URL_PAYMENT_SUCCESSFUL', 'Оплата завершена');
define('RFICB_RESULT_URL_PAYMENT_CANCELLED', 'Оплата заказа отменена');
define('RFICB_RESULT_URL_PAYMENT_PROCESS', 'Ожидается оплата');
define('RFICB_RESULT_URL_PAYMENT_RESERVED', 'Средства зарезервированны');

define('RFICB_PAY', 'Оплатить');
