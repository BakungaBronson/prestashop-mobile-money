{extends file='./helpers/form/form.tpl'}

{block name="input"}
    {if $input.type == 'file'}
        <div class="col-lg-9">
            <div class="form-group">
                <div class="col-lg-6">
                    <input id="{$input.name}" type="file" name="{$input.name}" class="hide" />
                    <div class="dummyfile input-group">
                        <span class="input-group-addon"><i class="icon-file"></i></span>
                        <input id="{$input.name}-name" type="text" class="disabled" name="filename" readonly />
                        <span class="input-group-btn">
                            <button id="{$input.name}-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
                                <i class="icon-folder-open"></i> {l s='Choose a file' mod='mobilemoney'}
                            </button>
                        </span>
                    </div>
                </div>
                {if isset($input.current_value) && $input.current_value}
                    <div class="col-lg-6">
                        <img src="{$uri}views/img/qr/{$input.current_value}" class="img-thumbnail" style="max-width: 200px"/>
                    </div>
                {/if}
            </div>
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#{$input.name}-selectbutton').click(function(e){
                        $('#{$input.name}').trigger('click');
                    });
                    $('#{$input.name}').change(function(e){
                        var val = $(this).val();
                        var file = val.split(/[\\/]/);
                        $('#{$input.name}-name').val(file[file.length-1]);
                    });
                });
            </script>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}