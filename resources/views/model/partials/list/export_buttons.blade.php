
<div class="listing-export clearfix">

    <div>
        <span class="title">
            {{ cms_trans('models.export.buttons-title') }}
        </span>

        @foreach ($availableExportKeys as $exportKey)

            <?php
                $exportData = $model->export->strategies[ $exportKey ];
            ?>

            <a class="btn btn-sm btn-default" href="{{ route("{$routePrefix}.export", [ $exportKey ]) }}" target="_blank">
                @if ($exportData->icon())
                    <i class="glyphicon glyphicon-{{ $exportData->icon() }}"></i>
                    &nbsp;
                @endif

                {{ $exportData->label() }}
            </a>
        @endforeach
    </div>
</div>
