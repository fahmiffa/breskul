<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AppContext;

class LoadAppContext
{
    protected AppContext $context;

    public function __construct(AppContext $context)
    {
        $this->context = $context;
    }

    public function handle(Request $request, Closure $next)
    {
        $this->context->load();

        config([
            'app.school_mode' => $this->context->get('school_mode', false),
            'app.jurnal' => $this->context->get('jurnal', false),
            'app.halaqoh' => $this->context->get('halaqoh', false),
            'app.rfid' => $this->context->get('rfid', false),
            'app.features'    => $this->context->get('features', []),
            'app.current_id'  => $this->context->get('id'),
        ]);

        return $next($request);
    }
}