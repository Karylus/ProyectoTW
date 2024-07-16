<?php
require_once '../src/Vista/html.php';

class cInfo
{
    private $data;
    private $request;

    public function __construct($data, $request)
    {
        $this->data = $data;
        $this->request = $request;
    }
    public function showerror($msg)
    {
        $this->data['error'] = $msg;
    }
    public function showinfo($msg)
    {
        $this->data['info'] = $msg;
    }
    public function home()
    {
        $this->data['home'] = "home";
    }
    public function servicios()
    {
        $this->data['servicios'] = "servicios";
    }

    public function log()
    {
        $numeroFilas = 20;
        $paginaActual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
        $filaInicio = ($paginaActual - 1) * $numeroFilas;
        $orden = "fecha_hora DESC";

        $logs = Log::obtenerLogs($filaInicio, $numeroFilas, $orden);

        $this->data['logs'] = $logs[0];
        
        $this->data['paginaActual'] = $paginaActual;
        $this->data['numeroPaginas'] = ceil($logs[1] / $numeroFilas);
    }

    public function render()
    {
        return HTMLrenderWeb($this->data);
    }
}
