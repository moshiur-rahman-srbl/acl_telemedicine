<?php
namespace App\Form\Contract;
use Illuminate\Support\Collection;

/**
 * Created by PhpStorm.
 * User: MD Eyasin
 * Date: 8/24/2019
 * Time: 12:27 PM
 */
interface InputFormInterface
{

    public function inputField(Collection $formFields);
}