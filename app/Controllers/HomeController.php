<?php

class HomeController extends Controller
{
    public function index()
    {
        $this->view('home/index', array(
            'title' => 'Indian Bank Simulator'
        ));
    }
}
