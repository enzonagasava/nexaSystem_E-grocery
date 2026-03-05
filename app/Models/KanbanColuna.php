<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanColuna extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant_content';

    protected $table = 'kanban_colunas';


    protected $fillable = [
        'kanban_quadro_id',
        'status_id',
        'descricao',
        'cor'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get the board that owns the column.
     */
    public function quadro(): BelongsTo
    {
        return $this->belongsTo(KanbanQuadro::class, 'kanban_quadro_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }


    /**
     * Get the cards for the column.
     */
    public function cards(): HasMany
    {
        return $this->hasMany(KanbanCard::class, 'kanban_coluna_id');
    }

    /**
     * Get the active cards for the column.
     */
    public function cardsAtivos(): HasMany
    {
        return $this->cards()->whereNull('deleted_at');
    }

    /**
     * Get the cards ordered by position.
     */
    public function cardsOrdenados(): HasMany
    {
        return $this->cards()->orderBy('ordem');
    }

    /**
     * Scope a query to only include columns of a specific board.
     */
    public function scopeDoQuadro($query, int $quadroId)
    {
        return $query->where('kanban_quadro_id', $quadroId);
    }


    /**
     * Get the CSS classes for the column.
     */
    public function getCssClassesAttribute(): array
    {
        $classes = [
            'header' => "border-{$this->cor}-200 bg-{$this->cor}-50",
            'text' => "text-{$this->cor}-700",
            'badge' => "bg-{$this->cor}-100 text-{$this->cor}-800",
        ];

        if ($this->cor_fundo) {
            $classes['fundo'] = $this->cor_fundo;
        }

        return $classes;
    }

    /**
     * Get the inline styles for the column.
     */
    public function getInlineStylesAttribute(): array
    {
        $styles = [];

        if ($this->cor_fundo) {
            $styles['backgroundColor'] = $this->cor_fundo;
        }

        return $styles;
    }

    /**
     * Reorder cards within this column.
     */
    public function reordenarCards(array $ordens): bool
    {
        foreach ($ordens as $cardId => $ordem) {
            KanbanCard::where('id', $cardId)
                ->where('kanban_coluna_id', $this->id)
                ->update(['ordem' => $ordem]);
        }

        return true;
    }

    /**
     * Get the next available order position.
     */
    public function getProximaOrdemAttribute(): int
    {
        $maxOrdem = $this->cards()->max('ordem');
        return ($maxOrdem ?? 0) + 1;
    }

    /**
     * Check if column is empty.
     */
    public function getIsEmptyAttribute(): bool
    {
        return $this->cards()->count() === 0;
    }

    /**
     * Get the count of cards in this column.
     */
    public function getTotalCardsAttribute(): int
    {
        return $this->cards()->count();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

    static::deleted(function ($coluna) {
        // Após a coluna ser deletada (soft delete), 
        // verifica se pode deletar o status
        if ($coluna->status_id) {
            $outrasColunas = KanbanColuna::where('status_id', $coluna->status_id)
                ->where('id', '!=', $coluna->id)
                ->exists();
            
            if (!$outrasColunas) {
                $coluna->status()->delete();
            }
        }
    });

        // Quando restaurar uma coluna, restaura também os cards
        static::restoring(function ($coluna) {
            $coluna->cards()->withTrashed()->restore();
        });
    }
}