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
            'factory_number' => 'ASCII(12)', // Заводской номер ККТ
            'current_date' => 'DATETIME(5)', // Текущие Дата\Время в ККТ
            'critical_error' => 'BYTE(1)', // Критические ошибки в ККТ
            // 0 – ошибок нет, 1 – присутствуют
            'status_printing_device' => 'BYTE(1)', // Статус Печатающего устройства
            // 0 – Корректный статус, бумага присутствует
            // 1 – Устройство не подключено
            // 2 – Отсутствует бумага
            // 3 – Замятие бумаги
            // 5 – Открыта крышка ПУ
            // 6 – Ошибка отрезчика ПУ
            // 7 – Аппаратная ошибка ПУ
            'fiscal_storage' => 'BYTE(1)', // Наличие ФН в ККТ
            // 1 – ФН подключен; 0 – ФН не подключен
            'phase_life' => 'BYTE(1)', // Фаза жизни ФН
            // 1 (0001) - Готовность к фискализации
            // 3 (0011) - Фискальный режим
            // 7 (0111) - Постфискальный режим, идет передача ФД в ОФД
            // 15 (1111) - Чтение данных из архива ФН
            'model_printing_device' => 'BYTE(1)' // ?
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
            'factory_number' => 'ASCII(12)',
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
            'software_version' => 'ASCII(N)',
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
            'model' => 'ASCII(N)',
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
            'fiscal_factory_number' => 'ASCII(N)',
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
            'fiscal_software_version' => 'ASCII(N)',
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
            'expiration_date' => 'DATETIME(3)',
            'available_registrations' => 'BYTE(1)',
            'сonducted_registrations' => 'BYTE(1)'
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
            'phase_life' => 'BYTE(1)', // Фаза жизни ФН
            // 1 (0001) – проведена настройка ФН
            // 3 (0011) – открыт фискальный режим
            // 7 (0111) – постфискальный режим
            // 15 (1111) – закончена передача ФД в ОФД
            'current_document' => 'BYTE(1)', // Текущий документ
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
            'document_data' => 'BYTE(1)', // Данные документа
            // 0 – нет данных документа
            // 1 – получены данные документа
            'shift_status' => 'BYTE(1)', // Состояние смены
            // 0 – смена закрыта
            // 1 – смена открыта
            'flags_warnings' => 'BYTE(1)', // Флаги и предупреждения
            // Приложение 3.
            'last_document_date' => 'DATETIME(5)', // Дата и время последнего документа
            'fiscal_number' => 'ASCII(16)', // Номер ФН
            'last_document_number' => 'UINTLE(4)', // Номер последнего ФД
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
            'registration_number' => 'ASCII(20)', // РН ККТ
            // Дополняется пробелами справа до длины 20 символов
            'inn' => 'ASCII(12)', // ИНН
            // Дополняется пробелами справа до длины 12 символов
            'operating_mode' => 'BYTE(1)', // Режимы работы ККТ
            // Битовая маска, каждый установленный бит означает соответствующий режим работы, см приложение 7
            'tax_regime' => 'BYTE(1)', // Режимы налогообложения
            // Битовая маска, каждый установленный бит означает возможность применения соответствующего режима налогообложения, см приложение 7
            'paying_agent' => 'BYTE(1)', // Признак платежного агента
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
            'configuration_version' => 'ASCII(N)', // Номер версии конфигурации ККТ
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
            'ip' => 'IP(4)', // IP адрес
            'mask' => 'IP(4)', // Маска подсети
            'gateway' => 'IP(4)', // Шлюз по умолчанию
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
            'exchange_status' => 'BYTE(1)', // Статус информационного обмена
            // Служебный параметр
            'read_message_status' => 'BYTE(1)', // Состояние чтения сообщения для ОФД
            // Служебный параметр
            'messages_number' => 'UINTLE(2)', // Количество сообщений для передачи в ОФД
            'document_number' => 'UINTLE(4)', // Номер документа для ОФД первого в очереди
            'document_date' => 'DATETIME(5)', // Дата-время документа для ОФД первого в очереди
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
            'characters_number' => 'BYTE(1)', // Количество символов в строке
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
            'last_errors' => 'BYTE(N)', // Буфер данных
        ];

        return $this->send('09', false, $structure);
    }
}