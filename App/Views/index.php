<?
/** @var \Core\Helpers\Scud $this */
$this->extend('template');
$this->setMeta('title', 'Hello framework - Any Apps easy.');
?>
<input type="button" value="Добавить фигуру" id="add">
<input type="button" value="Отрисовать" id="draw">
<br>
<br>
<form id="main-form" method="post" action="/">
    <?foreach ($params as $n => $param):?>
    <div class="row">
        <div class="param">
            <label for="">
                Фигура
                <select name="params[<?=$n?>][type]">
                    <option <?= $param['type'] == 'circle' ? 'selected' : ''?> value="circle">Круг</option>
                    <option <?= $param['type'] == 'rect' ? 'selected' : ''?> value="rect">Квадрат</option>
                </select>
            </label>
        </div>
        <div class="param">
            <label for="">
                Размер бордера
                <select name="params[<?=$n?>][params][border]">
                    <option <?= $param['params']['border'] == '1' ? 'selected' : ''?> value="1">1</option>
                    <option <?= $param['params']['border'] == '2' ? 'selected' : ''?> value="2">2</option>
                    <option <?= $param['params']['border'] == '3' ? 'selected' : ''?> value="3">3</option>
                    <option <?= $param['params']['border'] == '4' ? 'selected' : ''?> value="4">4</option>
                    <option <?= $param['params']['border'] == '5' ? 'selected' : ''?> value="5">5</option>
                </select>
            </label>
        </div>
        <div class="param">
            <label for="">
                Цвет(r/g/b)
                <input type="text" size="4" name="params[<?=$n?>][params][color][r]" value="<?= @$param['params']['color']['r']?>">&nbsp;
                <input type="text" size="4" name="params[<?=$n?>][params][color][g]" value="<?= @$param['params']['color']['g']?>">&nbsp;
                <input type="text" size="4" name="params[<?=$n?>][params][color][b]" value="<?= @$param['params']['color']['b']?>">
            </label>
        </div>
        <div class="param">
            <label for="">
                Положение
                <input type="text" size="4" name="params[<?=$n?>][params][position][x]" value="<?= @$param['params']['position']['x']?>">&nbsp;
                <input type="text" size="4" name="params[<?=$n?>][params][position][y]" value="<?= @$param['params']['position']['y']?>">&nbsp;
            </label>
        </div>
    </div>
    <br>
    <br>
    <?endforeach;?>
</form>
<img src="/image?<?=http_build_query(['params' => $params])?>" alt="NO IMAGE">
<script type="text/javascript">
    document.querySelector('#add').onclick = function (e) {
        var figs = document.querySelectorAll('.row');
        if(!figs.length)
            return;

        var row = figs[figs.length - 1];
        var new_row = row.innerHTML.replace(/params\[(\d+)]/ig, function (str, num, offset, s) {
            console.log(num);
            return 'params[' + (parseInt(num) + 1) + ']';
        });

        new_row += '<br><br>';
        var form = document.querySelector('#main-form');
        form.innerHTML = form.innerHTML + new_row;
    };

    document.querySelector('#draw').onclick = function () {
        document.querySelector('#main-form').submit();
    };
</script>

