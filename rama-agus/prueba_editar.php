<?php
require_once 'modelo/usuarios.php';

var_dump(Usuario::editar(1, [
    'Nombre' => 'Editado', 'Apellido' => 'Dos', 'Sexo' => 'Femenino', 'CUIL' => '20123456789',
    'NDoc' => '12345678', 'IdTdoc' => '1', 'FNacimiento' => '1991-06-21', 'Imagen' => '',
    'Oficios' => ['Pintor'], 'Telefono' => '3410000000', 'Email' => 'editado@correo.com',
    'Domicilio' => 'Calle 456', 'CodPostal' => '2000', 'Localidad' => 'Rosario'
]));