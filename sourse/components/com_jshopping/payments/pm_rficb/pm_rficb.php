<?php
//защита от прямого доступа
defined('_JEXEC') or die();

/**
 * Class pm_rficb
 */
class pm_rficb extends PaymentRoot{
    /** версия платежного интерфейса */
    const VERSION = '1.0';
    /** Url для совершения оплаты */
    const MERCHANT_URL = 'https://partner.rficb.ru/a1lite/input/';

    /**
     * @param array $params
     * @param array $pmconfigs
     */
    function showPaymentForm($params, $pmconfigs){
		include(dirname(__FILE__).'/paymentForm.php');
	}

    /**
     * Данный метод отвечает за настройки плагина в админ. части
     * @param $params Параметры настроек плагина
     */
    function showAdminFormParams($params){
        $module_params_array = array(
            'rficb_secret_key',
            'rficb_key',
            'transaction_end_status',
            'transaction_pending_status',
            'transaction_failed_status'
        );
        foreach($module_params_array as $module_param){
            if(!isset($params[$module_param]))
                $params[$module_param] = '';
        }
        $orders = JModelLegacy::getInstance('orders', 'JshoppingModel');
        $this->loadLanguageFile();
        include dirname(__FILE__) . '/adminparamsform.php';
	}

    /**
     * Подключение необходимого языкового файла для модуля
     */
    function loadLanguageFile(){
        $lang = JFactory::getLanguage();
        // определяем текущий язык
        $lang_tag = $lang->getTag();
        // папка с языковыми файлами модуля
        $lang_dir = JPATH_ROOT . '/components/com_jshopping/payments/pm_rficb/lang/';
        // переменная с полным именем языкового файла (с путём)
        $lang_file = $lang_dir . $lang_tag . '.php';
        // пытаемся подключить языковой файл, если такого нет - подключается по-умолчанию (en-GB.php)
        if(file_exists($lang_file))
            require_once $lang_file;
        else
            require_once $lang_dir . 'en-GB.php';
    }

    /**
     * Собирает информацию о заказе и настройках модуля, формирует поля xml и sign для POST запроса.
     * Из полученных данных генерирует HTML форму и выводит её.
     * @param array $pmconfigs Массив настроек модуля оплаты
     * @param object $order Объект текущего заказа, по которому происходит оформление
     */
    function showEndForm($pmconfigs, $order){
        // подгружаем языковой файл для описания возможных ошибок
        $this->loadLanguageFile();
        // если статус заказа по-умолчанию не совпадает со статусом заказа для незавершённых транзакций в настройках модуля оплаты, то выводим ошибку
        if($order->order_status != $pmconfigs['transaction_pending_status'])
            die(RFICB_REDIRECT_PENDING_STATUS_ERROR);
        /* далее получаем необходимые поля для инициализации платежа */

        $lang = JFactory::getLanguage()->getTag();
        switch($lang){
            case 'en_EN':
                $lang = 'en';
                break;
            case 'ru_RU':
                $lang = 'ru';
                break;
            default:
                $lang = 'ru';
                break;
        }
        $order_id = $order->order_id;
        $cost = $order->order_total;
        $name = 'Оплата заказа №' . $order_id;
        $key = $pmconfigs['rficb_key'];
        $merchant_url = self::MERCHANT_URL;
        $redirect_text = RFICB_REDIRECT_TO_PAYMENT_PAGE;
        $form = <<<FORM
<form action="$merchant_url" method="POST" id="paymentform">
    $redirect_text
    <input type="hidden" name="key" value="$key">
    <input type="hidden" name="cost" value="$cost"> 
    <input type="hidden" name="name" value="$name">
    <input type="hidden" name="order_id" value="$order_id">
</form>
<script type="text/javascript">
   document.getElementById('paymentform').submit();
</script>
FORM;
        echo $form;
    }

    /**
     * Вызывается при обработке запросов на Success Url, Fail Url и Result Url перед методом CheckTransaction().
     * Инициализирует массив с параметрами обработки входящего запроса, содержащий:
     * 'order_id' - идентификатор заказа
     * 'hash' - хэш строка для проверки подлинности
     * 'checkHash' - флаг, указывающий осуществлять ли проверку хэша
     * 'checkReturnParams' - флаг, указывающий осуществлять ли проверку входных параметров
     * @param $rficb_config Массив настроек способа оплаты
     * @return array Массив с параметрами обработки входящего запроса
     */
    function getUrlParams($rficb_config){
        $params = array();
        $input = JFactory::$application->input;
        $params['order_id'] = $input->getInt('order_id', null);
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = 0;
        return $params;
    }

    /**
     * Выполняет "проверку" транзакций (обработку запросов на Success Url, Fail Url и Result Url).
     * В результате возвращает двумерный массив с результатом проверки транзакции в виде array($rescode, $restext), где
     * $rescode - код результата транзакции (1 - оплата завершена, 2 - ожидание, 3 - отмена, 0 - ошибка)
     * $restext - текстовое сообщение о результате транзакции
     * В зависимости от переданного кода происходит создание заказа, изменение статуса заказа и email оповещение администратора магазина и покупателя.
     * Дальнейшее управление передаётся на метод nofityFinish().
     * @param $pmconfig Массив настроек модуля оплаты
     * @param $order Объект текущего заказа, по которому происходит оформление
     * @param $rescode Тип запроса (notify, return, cancel)
     * @return array Двумерный массив с результатом проверки транзакции
     */
    function checkTransaction($pmconfig, $order, $rescode){
        // подгружаем языковой файл для описания возможных ошибок
        $this->loadLanguageFile();
        // получаем объект, содержащий входные данные (GET и POST), исп. вместо deprecated JRequest::getInt('var')
        $inputObj = JFactory::$application->input;
        //print_r($inputObj);
        $in_data = array(  'tid'    =>  $inputObj->getString('tid', null),
                   'name'           =>  $inputObj->getString('name', null), 
                   'comment'        =>  $inputObj->getString('comment', null),
                   'partner_id'     =>  $inputObj->getString('partner_id', null),
                   'service_id'     =>  $inputObj->getString('service_id', null),
                   'order_id'       =>  $inputObj->getString('order_id', null),
                   'type'           =>  $inputObj->getString('type', null),
                   'partner_income' =>  $inputObj->getString('partner_income', null),
                   'system_income'  =>  $inputObj->getString('system_income', null),
                   'test'           =>  $inputObj->getString('test', null)
                );  
    $secret_key = $pmconfig['rficb_secret_key'];
    $transaction_sign = md5(implode('', array_values($in_data)) . $secret_key);
       
if ($transaction_sign !== $inputObj->getString('check', null) || empty($secret_key))
	die('bad sign');
  $type=$inputObj->getString('act', null);
      switch($type){
        case 'notify':  
          $order_amount = $order->order_total;
          $response_amount =$in_data['system_income'];
          if($response_amount < $order_amount) die('bad cost');
          if(!$order->order_created && ($order->order_status == $pmconfig['transaction_pending_status']))
            return array(1, RFICB_SUCCESS_URL_REQUEST_PASSED);
        // если заказ создан и статус Paid, значит запрос на Result Url уже был
          elseif($order->order_created && ($order->order_status == $pmconfig['transaction_end_status']))
              return array(1, RFICB_SUCCESS_URL_ORDER_ALREADY_PAID);
          // иначе что-то не так со статусами платежей
          else
              return array(0, RFICB_SUCCESS_URL_ORDER_STATUS_FAILED);
          break;
        case 'return':
          if($order->order_created && ($order->order_status == $pmconfig['transaction_end_status']))
              //return array(1, RFICB_SUCCESS_URL_ORDER_ALREADY_PAID);
              echo 'спасибо';
          // иначе что-то не так со статусами платежей
          else
              return array(0, RFICB_SUCCESS_URL_ORDER_STATUS_FAILED);
          break;
        case 'cancel':
            /* отработка запроса на Fail Url */
            // оплата отменена покупателем
            return array(3, RFICB_RESULT_URL_PAYMENT_CANCELLED);
            break;
        default:
            // на случай ошибки
            return array(0, RFICB_UNKNOWN_ERROR);
            break;
    } 

}

    /**
     * Данный метод выводит сообщение об успешной обработке запроса на Result Url.
     * Вызывается после метода checkTransaction()
     * @param $pmconfigs
     * @param $order
     * @param $rescode
     */
    function nofityFinish($pmconfigs, $order, $rescode){
        $msg = "OK$order->order_id";
        echo $msg;
	}
}
?>
