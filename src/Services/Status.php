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
            'factory_number' => 'ASCII(12)',
            'current_date' => 'DATETIME(5)',
            'critical_error' => 'BYTE(1)',
            'status_printing_device' => 'BYTE(1)',
            'fiscal_storage' => 'BYTE(1)',
            'phase_life' => 'BYTE(1)',
            'model_printing_device' => 'BYTE(1)'
        ];

        return  $this->send('01', false, $structure);
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
}