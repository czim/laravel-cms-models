<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Czim\Listify\Listify;
use Illuminate\Database\Eloquent\Model;

class TestOrderableBelongsToScoped extends Model
{
    use Listify;

    protected $fillable = [
        'name',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        $this->initListify([
            'scope' => $this->testActivatable(),
        ]);

        parent::__construct($attributes);
    }

    public function testActivatable()
    {
        return $this->belongsTo(TestActivatable::class);
    }

}
