<?php

namespace TerminalFaAPI\Services;

trait Status
{
    /**
     * Запрос статуса ККТ [0x01].
     *
     * @return mixed
     */
    public function state()
    {
        $structure = [
            'factory_number' => 'BINTEXT(12)', // Заводской номер ККТ
            'current_date' => 'BINDATE(5)', // Текущие Дата\Время в ККТ
            'critical_error' => 'BINDEC(1)', // Критические ошибки в ККТ
            // 0 – ошибок нет, 1 – присутствуют
            'status_printing_device' => 'BINDEC(1)', // Статус Печатающего устройства
            // 0 – Корректный статус, бумага присутствует
            // 1 – Устройство не подключено
            // 2 – Отсутствует бумага
            // 3 – Замятие бумаги
            // 5 – Открыта крышка ПУ
            // 6 – Ошибка отрезчика ПУ
            // 7 – Аппаратная ошибка ПУ
            'fiscal_storage' => 'BINDEC(1)', // Наличие ФН в ККТ
            // 1 – ФН подключен; 0 – ФН не подключен
            'phase_life' => 'BINDEC(1)', // Фаза жизни ФН
            // 1 (0001) - Готовность к фискализации
            // 3 (0011) - Фискальный режим
            // 7 (0111) - Постфискальный режим, идет передача ФД в ОФД
            // 15 (1111) - Чтение данных из архива ФН
            'model_printing_device' => 'BINDEC(1)' // ?
        ];

        return $this->send('01', false, $structure);
    }

    /**
     * Запрос заводского номера ККТ [0x02].
     *
     * @return mixed
     */
    public function factoryNumber()
    {
        $structure = [
            'factory_number' => 'BINTEXT(12)',
        ];

        return $this->send('02', false, $structure);
    }

    /**
     * Запрос версии ПО ККТ [0x03].
     *
     * @return mixed
     */
    public function softwareVersion()
    {
        $structure = [
            'software_version' => 'BINTEXT(N)',
        ];

        return $this->send('03', false, $structure);
    }

    /**
     * Запрос модели ККТ [0x04].
     *
     * @return mixed
     */
    public function model()
    {
        $structure = [
            'model' => 'BINTEXT(N)',
        ];

        return $this->send('04', false, $structure);
    }

    /**
     * Запрос заводского номера ФН [0x05].
     *
     * @return mixed
     */
    public function fiscalFactoryNumber()
    {
        $structure = [
            'fiscal_factory_number' => 'BINTEXT(N)',
        ];

        return $this->send('05', false, $structure);
    }

    /**
     * Запрос версии ПО ФН [0x06].
     *
     * @return mixed
     */
    public function fiscalSoftwareVersion()
    {
        $structure = [
            'fiscal_software_version' => 'BINTEXT(N)',
        ];

        return $this->send('06', false, $structure);
    }

    /**
     * Запрос срока действия ФН [0x07].
     *
     * @return mixed
     */
    public function fiscalExpirationDate()
    {
        $structure = [
            'expiration_date' => 'BINDATE(3)',
            'available_registrations' => 'BINDEC(1)',
            'сonducted_registrations' => 'BINDEC(1)'
        ];

        return $this->send('07', false, $structure);
    }

    /**
     * Запрос статуса ФН [0x08].
     *
     * @return mixed
     */
    public function fiscalState()
    {
        $structure = [
            'phase_life' => 'BINDEC(1)', // Фаза жизни ФН
            // 1 (0001) – проведена настройка ФН
            // 3 (0011) – открыт фискальный режим
            // 7 (0111) – постфискальный режим
            // 15 (1111) – закончена передача ФД в ОФД
            'current_document' => 'BINDEC(1)', // Текущий документ
            // 0 (00h) – Нет открытого документа
            // 1 (01h) – Отчѐт о регистрации ККТ
            // 2 (02h) – Отчѐт об открытии смены
            // 4 (04h) – Кассовый чек
            // 8 (08h) – Отчѐт о закрытии смены
            // 16 (10h) – отчѐт о закрытии фискального режима
            // 18 (12h) - Отчет об изменении параметров регистрации ККТ в связи с заменой ФН
            // 19 (13h) – Отчет об изменении параметров регистрации ККТ
            // 20 (14h) – Кассовый чек коррекции
            // 23 (17h) – Отчет о текущем состоянии расчетов
            'document_data' => 'BINDEC(1)', // Данные документа
            // 0 – нет данных документа
            // 1 – получены данные документа
            'shift_status' => 'BINDEC(1)', // Состояние смены
            // 0 – смена закрыта
            // 1 – смена открыта
            'flags_warnings' => 'BINDEC(1)', // Флаги и предупреждения
            // Приложение 3.
            'last_document_date' => 'BINDATE(5)', // Дата и время последнего документа
            'fiscal_number' => 'BINTEXT(16)', // Номер ФН
            'last_document_number' => 'BINDECREV(4)', // Номер последнего ФД
        ];

        return $this->send('08', false, $structure);
    }

    /**
     * Запрос текущих параметров регистрации ККТ [0x0A].
     *
     * @return mixed
     */
    public function registrationParameters()
    {
        $structure = [
            'registration_number' => 'BINTEXT(20)', // РН ККТ
            // Дополняется пробелами справа до длины 20 символов
            'inn' => 'BINTEXT(12)', // ИНН
            // Дополняется пробелами справа до длины 12 символов
            'operating_mode' => 'BINDEC(1)', // Режимы работы ККТ
            // Битовая маска, каждый установленный бит означает соответствующий режим работы, см приложение 7
            'tax_regime' => 'BINDEC(1)', // Режимы налогообложения
            // Битовая маска, каждый установленный бит означает возможность применения соответствующего режима налогообложения, см приложение 7
            'paying_agent' => 'BINDEC(1)', // Признак платежного агента
            // Битовая маска, каждый установленный бит означает соответствующий тип агента, см приложение 7
        ];

        return $this->send('0A', false, $structure);
    }

    /**
     * Запрос версии конфигурации ККТ [0x0B].
     *
     * @return mixed
     */
    public function configurationVersion()
    {
        $structure = [
            'configuration_version' => 'BINTEXT(N)', // Номер версии конфигурации ККТ
            // Строка в формате X.X.X
        ];

        return $this->send('0B', false, $structure);
    }

    /**
     * Запрос текущих параметров TCP/IP Ethernet интерфейса [0x0E].
     *
     * @return mixed
     */
    public function ethernetInterface()
    {
        $structure = [
            'ip' => 'BINIP(4)', // IP адрес
            'mask' => 'BINIP(4)', // Маска подсети
            'gateway' => 'BINIP(4)', // Шлюз по умолчанию
        ];

        return $this->send('0E', false, $structure);
    }

    /**
     * Запрос статуса информационного обмена с ОФД [0x50].
     *
     * @return mixed
     */
    public function exchangeStatus()
    {
        $structure = [
            'exchange_status' => 'BINDEC(1)', // Статус информационного обмена
            // Служебный параметр
            'read_message_status' => 'BINDEC(1)', // Состояние чтения сообщения для ОФД
            // Служебный параметр
            'messages_number' => 'BINDECREV(2)', // Количество сообщений для передачи в ОФД
            'document_number' => 'BINDECREV(4)', // Номер документа для ОФД первого в очереди
            'document_date' => 'BINDATE(5)', // Дата-время документа для ОФД первого в очереди
        ];

        return $this->send('50', false, $structure);
    }

    /**
     * Запрос количества символов в печатаемой строке (настройки) [0xBB].
     *
     * @return mixed
     */
    public function numberCharactersPrintedLine()
    {
        $structure = [
            'characters_number' => 'BINDEC(1)', // Количество символов в строке
        ];

        return $this->send('BB', false, $structure);
    }

    /**
     * Запрос последних ошибок ФН [0x09].
     *
     * @return mixed
     */
    public function lastErrors()
    {
        $structure = [
            'last_errors' => 'BINHEX(N)', // Буфер данных
        ];

        return $this->send('09', false, $structure);
    }
}