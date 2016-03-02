<?php

/**
 * Данная библиотека представляет собой набор классов-обёрток для curl_ функций.
 * 
 * @author Сергей Яруничев <xj5e34gnku22iiwi7q5d761zs2mkqx@gmail.com>
 * @license http://creativecommons.org/licenses/by/4.0/ Creative Commons Attribution 4.0 International (CC BY 4.0)
 * @version GIT: $Id$
 */

/**
 * Базовый класс для классов cURL, cURLMulti и cURLShare
 */
abstract class cURLBase
{
    /**
     * Ресур
     * 
     * @var resource
     */
    protected $resource;
}

/**
 * Класс представляет собой обёртку для работы с функциями curl_ в одном объекте.
 */
class cURL extends cURLBase
{
    /**
     * Конструктор класса.
     * 
     * Вызывает функцию <a href="http://php.net/manual/ru/function.curl-init.php" target="_blank">curl_init()</a>, инициализирует новый сеанс cURL.
     * 
     * @see http://php.net/manual/ru/function.curl-init.php
     * @param null|string $url Если указан, опция <b>CURLOPT_URL</b> будет автоматически установлена
     * в значение этого аргумента. Вы можете вручную установить эту опцию при помощи метода <b>setopt</b>.
     */
    public function __construct($url = null)
    {
        $this->resource = curl_init($url);
    }
    
    /**
     * Устанавливает параметр для сеанса cURL.
     * 
     * @see http://php.net/manual/ru/function.curl-setopt.php
     * @param int $option Устанавливаемый параметр CURLOPT_XXX.
     * @param mixed $value Значение параметра option.
     * @return bool Возвращает <b>TRUE</b> в случае успешного завершения или <b>FALSE</b> в случае возникновения ошибки.
     */
    public function setopt($option, $value)
    {
        // Если передан объект cURLShare
        if(defined('CURLSHOPT_SHARE') && $option === CURLSHOPT_SHARE && is_object($value) && $value instanceof cURLShare)
        {
            $value = $value->resource;
        }
        return curl_setopt($this->resource, $option, $value);
    }
    
    /**
     * Устанавливает несколько параметров для сеанса cURL.
     * 
     * Устанавливает несколько параметров для сеанса cURL. Эта функция полезна при установке большого
     * количества cURL-параметров без необходимости постоянно вызывать <a href="http://php.net/manual/en/function.curl-setopt.php" target="_blank">curl_setopt()</a>.
     * 
     * @see http://php.net/manual/ru/function.curl-setopt-array.php
     * @param array $options Массив, определяющий устанавливаемые параметры и их значения.
     * Ключи должны быть корректными константами для функции <a href="http://php.net/manual/en/function.curl-setopt.php" target="_blank">curl_setopt()</a> или их целочисленными эквивалентами.
     * @return bool Возвращает <b>TRUE</b>, если все параметры были успешно установлены. Если не удалось успешно установить
     * какой-либо параметр, немедленно возвращается значение <b>FALSE</b>, а последующие параметры в массиве options будут проигнорированы.
     */
    public function setoptArray($options)
    {
        // Если передан объект cURLShare
        if(defined('CURLSHOPT_SHARE') && array_key_exists(CURLSHOPT_SHARE, $options) && is_object($options[CURLSHOPT_SHARE]) && $options[CURLSHOPT_SHARE] instanceof cURLShare)
        {
            $options[CURLSHOPT_SHARE] = $options[CURLSHOPT_SHARE]->resource;
        }
        if(version_compare(PHP_VERSION, '5.1.3', '>='))
        {
            return curl_setopt_array($this->resource, $options);
        }
        if(!$options)
        {
            return false;
        }
        foreach($options as $option => $value)
        {
            if(!$this->setopt($option, $value))
            {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Завершает сеанс cURL.
     * 
     * Завершает сеанс cURL (вызывает <a href="http://php.net/manual/ru/function.curl-close.php" target="_blank">curl_close()</a>) и освобождает все ресурсы. Дескриптор также уничтожается.
     * 
     * @see http://php.net/manual/ru/function.curl-close.php
     */
    public function close()
    {
        curl_close($this->resource);
    }
    
    /**
     * Деструктор.
     * 
     * Завершает сеанс cURL.
     */
    public function __destruct()
    {
        $this->close();
    }
    
    /**
     * Копирует дескриптор cURL вместе со всеми его настройками.
     * 
     * Вызывается после клонирования объекта, вызывает <a href="http://php.net/manual/ru/function.curl-copy-handle.php" target="_blank">curl_copy_handle()</a>,
     * копирует дескриптор cURL вместе со всеми его настройками.
     * 
     * @see http://php.net/manual/ru/function.curl-copy-handle.php
     */
    public function __clone()
    {
        $this->resource = curl_copy_handle($this->resource);
    }
    
    /**
     * Возвращает код последней ошибки.
     * 
     * Возвращает код ошибки последней операции cURL.
     * 
     * @see http://php.net/manual/ru/function.curl-errno.php
     * @return int Возвращает номер ошибки или 0 (ноль), если ошибки не произошло.
     */
    public function errno()
    {
        return curl_errno($this->resource);
    }
    
    /**
     * Возвращает строку с описанием последней ошибки текущего сеанса.
     * 
     * Возвращает понятное сообщение об ошибке для последней операции cURL.
     * 
     * @see http://php.net/manual/ru/function.curl-error.php
     * @return string Возвращает сообщение об ошибке или '' (пустую строку), если ошибки не произошло.
     */
    public function error()
    {
        return curl_error($this->resource);
    }
    
    /**
     * Кодирует строку согласно RFC 3986.
     * 
     * Данная функция кодирует строку согласно <a href="http://www.faqs.org/rfcs/rfc3986" target="_blank">RFC 3986</a>.
     * Так как данная функция появиласть только в php версии 5.5.0, то на более ранних версиях используется функция <a href="http://php.net/manual/ru/function.rawurlencode.php" target="_blank">rawurlencode()</a>.
     * 
     * @see http://php.net/manual/ru/function.curl-escape.php
     * @see http://www.faqs.org/rfcs/rfc3986
     * @param string $str Строка, которую необходимо закодировать
     * @return string Возвращает закодированную строку
     */
    public function escape($str)
    {
        if(version_compare(PHP_VERSION, '5.5.0', '>='))
        {
            return curl_escape($this->resource, $str);
        }
        return rawurlencode($str);
    }
    
    /**
     * Декодирует URL-кодированную строку согласно RFC 3986.
     * 
     * Декодирует URL-кодированную строку согласно <a href="http://www.faqs.org/rfcs/rfc3986" target="_blank">RFC 3986</a>.
     * Так как данная функция появиласть только в php версии 5.5.0, то на более ранних версиях используется функция <a href="http://php.net/manual/ru/function.rawurldecode.php" target="_blank">rawurldecode()</a>.
     * 
     * @see http://php.net/manual/ru/function.curl-unescape.php
     * @see http://www.faqs.org/rfcs/rfc3986
     * @param string $str Закодированная строка
     * @return string Возвращает декодированную строку
     */
    public function unescape($str)
    {
        if(version_compare(PHP_VERSION, '5.5.0', '>='))
        {
            return curl_unescape($this->resource, $str);
        }
        return rawurldecode($str);
    }
    
    /**
     * Возвращает версию cURL.
     * 
     * @see http://php.net/manual/ru/function.curl-version.php
     * @param int $age
     * @return array Возвращает ассоциативный массив со следующими элементами:
     * <ul>
     * <li><b>version_number</b> &mdash; 24-битный номер версии cURL;</li>
     * <li><b>version</b> &mdash; Номер версии cURL, в виде строки;</li>
     * <li><b>ssl_version_number</b> &mdash; 24-битный номер версии OpenSSL;</li>
     * <li><b>ssl_version</b> &mdash; Номер версии OpenSSL, в виде строки;</li>
     * <li><b>libz_version</b> &mdash; Номер версии zlib, в виде строки;</li>
     * <li><b>host</b> &mdash; Информация о хосте, где была собрана cURL;</li>
     * <li><b>age</b>;</li>
     * <li><b>features</b> &mdash; Битовая маска констант CURL_VERSION_XXX</li>
     * <li><b>protocols</b> &mdash; Массив поддерживаемых протоколов cURL</li>
     * </ul>
     */
    public static function version($age = CURLVERSION_NOW)
    {
        return curl_version($age);
    }
    
    /**
     * Выполняет запрос cURL.
     * 
     * Выполняет запрос cURL. Этот метод должен вызываться после установки всех необходимых параметров.
     * 
     * @see http://php.net/manual/ru/function.curl-exec.php
     * @return mixed Возвращает <b>TRUE</b> в случае успешного завершения или <b>FALSE</b> в случае возникновения ошибки.
     * Однако, если установлена опция <b>CURLOPT_RETURNTRANSFER</b>, при успешном завершении будет возвращен результат, а при неудаче &mdash; <b>FALSE</b>.
     */
    public function exec()
    {
        return curl_exec($this->resource);
    }
    
    /**
     * Возвращает информацию о последней операции.
     * 
     * Возвращает информацию о последней операции.
     * 
     * @see http://php.net/manual/ru/function.curl-getinfo.php
     * @param int $opt
     * @return mixed Если параметр opt указан, то возвращается его значение. Иначе, возвращается
     * ассоциативный массив со следующими индексами (которые соответствуют значениям аргумента opt), или FALSE в случае ошибки.
     */
    public function getinfo($opt = 0)
    {
        if($opt !== 0)
        {
            return curl_getinfo($this->resource, $opt);
        }
        return curl_getinfo($this->resource);
    }
    
    /**
     * Сбрасывает все параметры сеанса cURL, возвращая их к стандартным значениям.
     * 
     * Сбрасывает все параметры сеанса cURL, возвращая их к стандартным значениям.
     * Данный метод доступен начиная с php версии 5.5.0! На более ранних версиях его вызов не приведёт ни к какому результату.
     * 
     * @see http://php.net/manual/ru/function.curl-reset.php
     */
    public function reset()
    {
        if(version_compare(PHP_VERSION, '5.5.0', '>='))
        {
            curl_reset($this->resource);
        }
    }
    
    /**
     * Возвращает результат операции.
     * 
     * Возвращает результат операции, если была установлена опция <b>CURLOPT_RETURNTRANSFER</b>.
     * 
     * @see http://php.net/manual/ru/function.curl-multi-getcontent.php
     * @return string Возвращает содержимое cURL дескриптора, если была использована опция <b>CURLOPT_RETURNTRANSFER</b>.
     */
    public function getcontent()
    {
        return curl_multi_getcontent($this->resource);
    }
    
    /**
     * Служит для постановки и снятия соеднинения с паузы.
     * 
     * Служит для постановки и снятия соеднинения с паузы.
     * Данный метод доступен начиная с php версии 5.5.0! На более ранних версиях его вызов не приведёт ни к какому результату; метод всегда возвращает <b>NULL</b>.
     * 
     * @see http://php.net/manual/ru/function.curl-pause.php
     * @param int $bitmask Одна из констант <a href="https://curl.haxx.se/libcurl/c/curl_easy_pause.html" target="_blank"><b>CURLPAUSE_*</b></a>.
     * @return int Возвращает код ошибки, который соответствует одной из
     * констант <a href="https://curl.haxx.se/libcurl/c/libcurl-errors.html" target="_blank"><b>CURLE_*</b></a>.
     * В случае успеха возвращает значение, которое соответствет константе <b>CURLE_OK</b>.
     */
    public function pause($bitmask)
    {
        if(version_compare(PHP_VERSION, '5.5.0', '>='))
        {
            return curl_pause($this->resource, $bitmask);
        }
        return null;
    }

    /**
     * Возвращет строку с описанием указанного кода ошибки.
     * 
     * Возвращет строку с описанием указанного кода ошибки.
     * Данный метод доступен начиная с php версии 5.5.0! На более ранних версиях его вызов не приведёт ни к какому результату; метод всегда возвращает <b>NULL</b>.
     * 
     * @see http://php.net/manual/ru/function.curl-strerror.php
     * @param int $errornum <a href="https://curl.haxx.se/libcurl/c/libcurl-errors.html" target="_blank">Код ошибки CURLE</a>,
     * описание которой необходимо получить.
     * @return string Возвращет строку с описанием указанного кода ошибки
     * CURLE или возвращает <b>NULL</b>, если указанного кода ошибки не существует.
     */
    public static function strerror($errornum)
    {
        if(version_compare(PHP_VERSION, '5.5.0', '>='))
        {
            return curl_strerror($this->resource, $option, $value);
        }
        return null;
    }
}

/**
 * Класс представляет собой обёртку для curl_multi функций.
 */
class cURLMulti extends cURLBase
{
    /**
     * Конструктор класса.
     * 
     * Вызывает <a href="http://php.net/manual/ru/function.curl-multi-init.php" target="_blank">curl_multi_init</a>.
     *
     * @see http://php.net/manual/ru/function.curl-multi-init.php
     */
    public function __construct()
    {
        $this->resource = curl_multi_init();
    }

    /**
     * Добавляет обычный cURL дескриптор к набору cURL дескрипторов.
     *
     * @see http://php.net/manual/ru/function.curl-multi-add-handle.php
     * @param resource|\cURL $ch Дескриптор cURL, полученный из curl_init() или объект класса cURL.
     * @return int Возвращает 0 при успехе, или один из кодов ошибок CURLM_XXX.
     */
    public function addHandle($ch)
    {
        if(is_resource($ch))
        {
            return curl_multi_add_handle($this->resource, $ch);
        }
        elseif(is_object($ch) && $ch instanceof cURL)
        {
            return curl_multi_add_handle($this->resource, $ch->resource);
        }
        return CURLM_BAD_HANDLE;
    }

    /**
     * Закрывает набор cURL дескрипторов.
     * 
     * @see http://php.net/manual/ru/function.curl-multi-close.php
     */
    public function close()
    {
        curl_multi_close($this->resource);
    }
    
    /**
     * Деструктор.
     * 
     * Вызывает <a href="http://php.net/manual/ru/function.curl-multi-close.php" target="_blank">curl_multi_close()</a>.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Запускает под-соединения текущего дескриптора cURL.
     *
     * Обрабатывает каждый дескриптор в стеке. Этот метод может быть вызван вне зависимости от необходимости дескриптора читать или записывать данные.
     *
     * @see http://php.net/manual/ru/function.curl-multi-exec.php
     * @param int $still_running Ссылка на флаг, указывающий, идут ли еще какие-либо действия.
     * @return int Код cURL, указанный в <a href="http://php.net/manual/ru/curl.constants.php" target="_blank">"Предопределенных константах" cURL</a>.
     * <b>Замечание:</b>
     * Здесь возвращаются ошибки, относящиеся только ко всему стеку.
     * Проблемы все еще могут произойти на индивидуальных запросах, даже когда эта функция возвращает <b>CURLM_OK</b>.
     */
    public function exec(&$still_running)
    {
        return curl_multi_exec($this->resource, $still_running);
    }

    /**
     * Возвращает информацию о текущих операциях.
     *
     * Опрашивает набор дескрипторов о наличии сообщений или информации от индивидуальных передач.
     * Сообщения могут включать такую информацию как код ошибки передачи или прост факт завершения передачи.
     * Повторяющиеся вызовы этой функции будут каждый раз возвращать новый результат,
     * пока не будет возвращено <b>FALSE</b> в качестве сигнала окончания сообщений. Целое число,
     * содержащееся в <b>msgs_in_queue</b>, указывает количество оставшихся сообщений после вызова данной функции.
     *
     * @see http://php.net/manual/ru/function.curl-multi-info-read.php
     * @param int $msgs_in_queue Количество оставшихся сообщений в очереди. Данный параметр доступен начиная с php версии 5.2.0.
     * @return array В случае успеха, возвращает ассоциативный массив сообщений, или FALSE в случае неудачи.
     * <table>
     * <caption>Содержимое возвращаемого массива</caption>
     * <thead>
     * <tr>
     * <th>Ключ:</th>
     * <th>Значение:</th>
     * </tr>
     * </thead>
     * <tbody>
     * <tr>
     * <td><em>msg</em></td>
     * <td>Константа <strong>CURLMSG_DONE</strong>. Остальные возвращаемые значения пока недоступны.</td>
     * </tr>
     * <tr>
     * <td><em>result</em></td>
     * <td>Одна из констант <b>CURLE_*</b>. Если все OK, результатом будет константа <b>CURLE_OK</b>.</td>
     * </tr>
     * <tr>
     * <td><em>handle</em></td>
     * <td>Ресурс типа curl, указывающий на дескриптор, к которому он относится.</td>
     * </tr>
     * </tbody>
     * </table>
     */
    public function infoRead(&$msgs_in_queue = null)
    {
        if(version_compare(PHP_VERSION, '5.2.0', '>='))
        {
            return curl_multi_info_read($this->resource, $msgs_in_queue);
        }
        return curl_multi_info_read($this->resource);
    }
    
    /**
     * Удаляет cURL дескриптор из набора cURL дескрипторов.
     * 
     * Удаляет указанный дескриптор <b>ch</b> из указанного набора дескрипторов <b>mh</b>.
     * После того, как дескриптор <b>ch</b> удален, его можно снова совершенно легально использовать
     * в функции <a href="http://php.net/manual/ru/function.curl-exec.php" target="_blank">curl_exec()</a>.
     * Удаление дескриптора <b>ch</b> во время использования также остановит текущую передачу, идущую на этом дескрипторе.
     * 
     * @see http://php.net/manual/ru/function.curl-multi-remove-handle.php
     * @param resource $ch Дескриптор cURL, полученный из curl_init().
     * @return int В случае успеха возвращает 0 или одну из констант CURLM_XXX, где XXX - код ошибки.
     */
    public function removeHandle($ch)
    {
        return curl_multi_remove_handle($this->resource, $ch);
    }
    
    /**
     * Ждет активности на любом curl_multi соединении.
     * 
     * Блокирует выполнение скрипта, пока какое-либо из curl_multi соединений не станет активным.
     * 
     * @see http://php.net/manual/ru/function.curl-multi-select.php
     * @param float $timeout Время, в секундах, для ожидания ответа.
     * @return int В случае успеха, возвращает количество дескрипторов,содержащихся в наборах дескрипторов.
     * Может вернуть 0, если не было активности ни на одном дескрипторе. В случае неудачи эта функция
     * вернет -1 при ошибке выборки (из нижележащего системного вызова выборки).
     */
    public function select($timeout = 1.0)
    {
        return curl_multi_select($this->resource, $timeout);
    }
    
    /**
     * Задаёт значение указанного параметра для мультидескриптора cURL, представленного текущим объектом.
     * Данный метод доступен начиная с php версии 5.5.0! На более ранних версиях его вызов не приведёт ни к какому результату; метод всегда возвращает <b>FALSE</b>.
     * 
     * @see http://php.net/manual/ru/function.curl-multi-setopt.php
     * @param int $option Параметр, для которого будет установленно значение - одна из констант <b>CURLMOPT_*</b>.
     * @param mixed $value Устанавливаемое значение.
     * @return boolean Возвращает <b>TRUE</b> в случае успешного завершения или <b>FALSE</b> в случае возникновения ошибки.
     */
    public function setopt($option, $value)
    {
        if(version_compare(PHP_VERSION, '5.5.0', '>='))
        {
            return curl_multi_setopt($this->resource, $option, $value);
        }
        return false;
    }
    
    /**
     * Возвращет строку с описанием указанного кода ошибки.
     * Данный метод доступен начиная с php версии 5.5.0! На более ранних версиях его вызов не приведёт ни к какому результату; метод всегда возвращает <b>NULL</b>.
     * 
     * @see http://php.net/manual/ru/function.curl-multi-strerror.php
     * @param int $errornum <a href="https://curl.haxx.se/libcurl/c/libcurl-errors.html" target="_blank">Код ошибки CURLM</a>, описание которой необходимо получить.
     * @return string Возвращет строку с описанием указанного кода ошибки
     * CURLM или возвращает <b>NULL</b>, если указанного кода ошибки не существует.
     */
    public static function strerror($errornum)
    {
        if(version_compare(PHP_VERSION, '5.5.0', '>='))
        {
            return curl_multi_setopt($this->resource, $option, $value);
        }
        return null;
    }
}

/**
 * Класс представляет собой обёртку для curl_share функций.
 * Так как функции curl_share стали доступны только в php версии 5.5.0, то на более ранних версиях использовать этот класс не имеет смысла.
 */
class cURLShare extends cURLBase
{
    /**
     * Конструктор класса.
     * 
     * Вызывает функцию <a href="http://php.net/manual/en/function.curl-share-init.php" target="_blank">curl_share_init()</a>.
     */
    public function __construct()
    {
        if(version_compare(PHP_VERSION, '5.5.0', '>='))
        {
            $this->resource = curl_share_init();
        }
    }

    /**
     * Задаёт значение опции для распределённого ресурса cURL.
     *
     * @see http://php.net/manual/en/function.curl-share-setopt.php
     * @param int $option Идентификатор опции:
     * <ul>
     * <li><b>CURLSHOPT_SHARE</b> &mdash; указывает тип данных, к которым будет открыт общий доступ;</li>
     * <li><b>CURLSHOPT_UNSHARE</b> &mdash; указывает тип данных, к которым будет закрыт общий доступ.</li>
     * @param string $value Одно из значений констант:
     * <ul>
     * <li><b>CURL_LOCK_DATA_COOKIE</b> &mdash; общий доступ для данных cookie.</li>
     * <li><b>CURL_LOCK_DATA_DNS</b> &mdash; общий доступ к DNS-кэшу. Обратите внимание, что если вы используете мультиресурс cURL,
     * то все ресурсы cURL, включённые в данный ресурс, имеют общий доступ к DNS-кэшу по-умолчанию.</li>
     * <li><b>CURL_LOCK_DATA_SSL_SESSION</b> &mdash; общий доступ к идентификаторам SSL-сессий.
     * Если общий доступ открыт, то это экономит время, на SSL-хэндшейках при повторном подключении к серверам, с которыми ранее уже
     * устанавливалось соединение. Обратите внимание, что одиночный cURL-ресурс и так использует идентификаторы SSL повторно.</li>
     * </ul>
     * @return bool Возвращает <b>TRUE</b> в случае успеха, иначе &mdash; <b>FALSE</b>.
     */
    public function setopt($option, $value)
    {
        if(version_compare(PHP_VERSION, '5.5.0', '>='))
        {
            return curl_share_setopt($this->resource, $option, $value);
        }
        return false;
    }

    /**
     * Закрывает открытый распределённый ресурс cURL.
     */
    public function close()
    {
        if(version_compare(PHP_VERSION, '5.5.0', '>='))
        {
            curl_share_close($this->resource);
        }
    }

    /**
     * Деструктор.
     */
    public function __destruct()
    {
        $this->close();
    }
}