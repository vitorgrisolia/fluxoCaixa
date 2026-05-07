<?php

namespace App\Observers;

use App\Support\Auditoria\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class ModelAuditObserver
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $beforeSnapshots = [];

    public function created(Model $model): void
    {
        AuditLogger::logModel('CRIAR', $model, null, $model->getAttributes());
    }

    public function updating(Model $model): void
    {
        $this->beforeSnapshots[spl_object_id($model)] = $model->getOriginal();
    }

    public function updated(Model $model): void
    {
        $key = spl_object_id($model);
        $antes = $this->beforeSnapshots[$key] ?? $model->getOriginal();
        unset($this->beforeSnapshots[$key]);

        $depois = $model->getAttributes();
        if (empty($model->getChanges())) {
            return;
        }

        $camposAlterados = array_keys($model->getChanges());
        $antesFiltrado = [];
        $depoisFiltrado = [];

        foreach ($camposAlterados as $campo) {
            $antesFiltrado[$campo] = $antes[$campo] ?? null;
            $depoisFiltrado[$campo] = $depois[$campo] ?? null;
        }

        AuditLogger::logModel('ATUALIZAR', $model, $antesFiltrado, $depoisFiltrado);
    }

    public function deleting(Model $model): void
    {
        $this->beforeSnapshots[spl_object_id($model)] = $model->getAttributes();
    }

    public function deleted(Model $model): void
    {
        $key = spl_object_id($model);
        $antes = $this->beforeSnapshots[$key] ?? $model->getAttributes();
        unset($this->beforeSnapshots[$key]);

        AuditLogger::logModel('EXCLUIR', $model, $antes, null);
    }
}
