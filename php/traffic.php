<?php

class traffic {
    private $traffic;

    public function __construct()
    {
        $traffic = null;
        
        if (!empty(lib::$data->data->traffic)) {
            $traffic = lib::$data->data->traffic;
        }
        
        $this->traffic = new trafficController($traffic);
    }

    /**
     * Metodo solicita todos os traffics em uso
     */
    public function getUsingTraffics ()
    {
        return lib::$return = ['status' => true, 'traffics' => $this->traffic->getUsingTraffics()];
    }

    public function removeTraffic () 
    {
        return $this->traffic->delete('traffic');
    }

    /**
     * Metodo insere um registro no traffics
     * valida se as informações informadas para cadastro de cliente e veiculo estão corretos
     * e atualiza o dono do veiculo caso o mesmo ja possua um cadastro
     */
    public function new () 
    {
        $client = new clientController(lib::$data->data->client);
        $vehicle = new vehicleController(lib::$data->data->vehicle);
        $config = new configController;
        
        $diff = $config->parking_space - $this->traffic->parking_space;
        
        if ($diff < 0 || $this->traffic->parking_space <= 0) {
            lib::$return['err'] = 'Vaga de estacionamento inexistente';
            return false;
        }

        $free = $this->traffic->spotIsFree($this->traffic->parking_space);

        if ($free === false) {
            lib::$return['err'] = 'Vaga de estacionamento ocupada';
            return false;
        }
        
        if ($vehicle->validPlate($vehicle->license_plate) === false) {
            return false;
        }

        if ($this->traffic->isBusy($vehicle->license_plate) === true) {
            return false;
        }

        if ($client->insertOrUpdate() === false) {
            return false;
        }        

        lib::$return['client'] = $client->getId();

        if ($vehicle->insertOrUpdate() === false) {
            return false;
        }

        lib::$return['vehicle'] = $vehicle->getId();

        $this->traffic->setClient($client->getId());
        $this->traffic->setVehicle($vehicle->getId());
        
        $result = $this->traffic->insertOrUpdate();
        
        if ($result === false) return false;
        
        lib::$return['status'] = true;
        return lib::$return['traffic'] = $this->traffic->getTraffic();
    }
}