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
            'shift_status' => 'INT(1)', // Состояние смены
            // 0 – смена закрыта, 1 – смена открыта
            'shift_number' => 'INTLE(2)', // Номер смены
            // Если смена закрыта, то – номер последней закрытой смены, если открыта, то номер текущей смены
            'check_number' => 'INTLE(2)', // Номер чека
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
        $hex = $this->dechex($withoutPrinting);

        return $this->send('21', $hex);
    }

    /**
     * Передать данные кассира [0x2F].
     *
     * $fullName - ФИО Кассира. Не более 64 символов
     * $inn - ИНН Кассира. Параметр необязательный, если передается, то строго 12 символов
     *
     * @return mixed
     */
    public function sendCashierDetails($fullName, $inn = null)
    {
        $data = implode([
            $this->tlv(1021, $fullName),
            $this->tlv(1203, $inn)
        ]);

        return $this->send('2F', $data);
    }
}