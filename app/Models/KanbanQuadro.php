<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanQuadro extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant_content';

    protected $table = 'kanban_quadros';


    protected $fillable = [
        'id',
        'user_id',
        'permissao_users',
        'nome',
        'descricao',
        'tipo',
        'favoritos',
        'is_active',
        'ordem',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissao_users' => 'array',
        'favoritos' => 'array',
        'is_active' => 'boolean',
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

    /**
     * Get the user that owns the board.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function colunas()
    {
        return $this->hasMany(KanbanColuna::class, 'kanban_quadro_id');
    }

    public function statuses()
    {
        return $this->hasManyThrough(
            Status::class,
            KanbanColuna::class,
            'kanban_quadro_id', // Foreign key on kanban_colunas
            'id',               // Foreign key on status
            'id',               // Local key on kanban_quadros
            'status_id'         // Local key on kanban_colunas
        );
    }
    
    /**
     * Get the columns for the board (ordered by position).
     */
    public function colunasOrdenadas(): HasMany
    {
        return $this->colunas()->orderBy('ordem');
    }

    /**
     * Get the active columns.
     */
    public function colunasAtivas(): HasMany
    {
        return $this->colunas()->whereNull('deleted_at');
    }

    /**
     * Scope a query to only include active boards.
     */
    public function scopeAtivos($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include boards of a specific type.
     */
    public function scopeDoTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope a query to only include boards for a specific user.
     */
    public function scopeDoUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include boards that a user has access to.
     */
    public function scopeComAcesso($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->orWhereJsonContains('permissao_users', $userId);
    }

    /**
     * Check if a user has access to this board.
     */
    public function usuarioTemAcesso(int $userId): bool
    {
        return $this->user_id === $userId 
            || (is_array($this->permissao_users) && in_array($userId, $this->permissao_users));
    }

    /**
     * Add a user to the board permissions.
     */
    public function adicionarPermissao(int $userId): self
    {
        $permissoes = $this->permissao_users ?? [];
        if (!in_array($userId, $permissoes)) {
            $permissoes[] = $userId;
            $this->permissao_users = $permissoes;
            $this->save();
        }
        return $this;
    }

    /**
     * Remove a user from the board permissions.
     */
    public function removerPermissao(int $userId): self
    {
        $permissoes = $this->permissao_users ?? [];
        $this->permissao_users = array_values(array_diff($permissoes, [$userId]));
        $this->save();
        return $this;
    }

    /**
     * Toggle favorite status for a user.
     */
    public function toggleFavorito(int $userId): bool
    {
        $favoritos = $this->favoritos ?? [];
        
        if (in_array($userId, $favoritos)) {
            $this->favoritos = array_values(array_diff($favoritos, [$userId]));
            $isFavorito = false;
        } else {
            $favoritos[] = $userId;
            $this->favoritos = $favoritos;
            $isFavorito = true;
        }
        
        $this->save();
        return $isFavorito;
    }

    /**
     * Check if board is favorited by a user.
     */
    public function isFavoritoPara(int $userId): bool
    {
        $favoritos = $this->favoritos ?? [];
        return in_array($userId, $favoritos);
    }

    /**
     * Get the total number of cards in this board.
     */
    public function getTotalCardsAttribute(): int
    {
        return $this->colunas()->withCount('cards')->get()->sum('cards_count');
    }

    /**
     * Get the board statistics.
     */
    public function getEstatisticasAttribute(): array
    {
        $stats = [];
        foreach ($this->colunas as $coluna) {
            $stats[] = [
                'coluna_id' => $coluna->id,
                'coluna_titulo' => $coluna->titulo,
                'total_cards' => $coluna->cards()->count(),
                'limite_wip' => $coluna->wip_limit,
                'percentual' => $coluna->wip_limit ? round(($coluna->cards()->count() / $coluna->wip_limit) * 100) : null,
            ];
        }
        return $stats;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Quando deletar um quadro, deleta também as colunas
        static::deleting(function ($quadro) {
            if ($quadro->isForceDeleting()) {
                $quadro->colunas()->forceDelete();
            } else {
                $quadro->colunas()->delete();
            }
        });

        // Quando restaurar um quadro, restaura também as colunas
        static::restoring(function ($quadro) {
            $quadro->colunas()->withTrashed()->restore();
        });
    }
}