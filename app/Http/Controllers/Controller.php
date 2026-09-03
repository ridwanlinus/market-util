<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function companyId(): int
    {
        return (int) auth()->user()->company_id;
    }
}