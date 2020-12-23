<?php

namespace TerminalFaAPI\Services;

trait Shift
{
    /**
     * Запрос параметров текущей смены [0x20].
     *
     * @return mixed
     */
    public function parametersСurrentShift()
    {
        $structure = [
            'shift_status' => 'BINDEC(1)', // Состояние смены
            // 0 – смена закрыта, 1 – смена открыта
            'shift_number' => 'BINDECREV(2)', // Номер смены
            // Если смена закрыта, то – номер последней закрытой смены, если открыта, то номер текущей смены
            'check_number' => 'BINDECREV(2)', // Номер чека
            // Если смена закрыта, то число документов в предыдущей закрытой смене (0, если это первая смена). Если смена открыта, но нет ни одного чека, то 0. В остальных случаях – номер последнего сформированного чека
        ];

        return $this->send('20', false, $structure);
    }

    /**
     * Начать открытие смены [0x21].
     *
     * $withoutPrinting - Формировать отчет без вывода на печать
     * true - не печатать чек
     * false - напечатать чек
     *
     * @return mixed
     */
    public function startOpeningShift($withoutPrinting = true)
    {
        return $this->send('21', $this->dechex($withoutPrinting));
    }

    /**
     * Передать данные кассира [0x2F].
     *
     * $fullName - ФИО Кассира. Не более 64 символов
     * $inn - ИНН Кассира. Параметр необязательный, если передается, то строго 12 символов
     *
     * @return mixed
     */
    public function sendCashierDetails($fullName, $inn = '            ')
    {
        $data = implode([
            $this->tlv(1021, $fullName),
            $this->tlv(1203, $inn)
        ]);

        return $this->send('2F', $data);
    }

    /**
     * Открыть смену [0x22].
     *
     * @return mixed
     */
    public function openShift()
    {
        $structure = [
            'shift_number' => 'BINDECREV(2)', // Номер открытой смены
            'document_number' => 'BINDECREV(4)', // Номер ФД
            'fiscal_feature' => 'BINDECREV(4)', // Фискальный признак
        ];

        return $this->send('22', false, $structure);
    }

    /**
     * Начать закрытие смены [0x29].
     *
     * $withoutPrinting - Формировать отчет без вывода на печать
     * true - не печатать чек
     * false - напечатать чек
     *
     * @return mixed
     */
    public function startСlosingShift($withoutPrinting = true)
    {
        return $this->send('29', $this->dechex($withoutPrinting));
    }

    /**
     * Закрыть смену [0x2A].
     *
     * @return mixed
     */
    public function closeShift()
    {
        $structure = [
            'shift_number' => 'BINDECREV(2)', // Номер закрытой смены
            'document_number' => 'BINDECREV(4)', // Номер ФД
            'fiscal_feature' => 'BINDECREV(4)', // Фискальный признак
        ];

        return $this->send('2A', false, $structure);
    }
}