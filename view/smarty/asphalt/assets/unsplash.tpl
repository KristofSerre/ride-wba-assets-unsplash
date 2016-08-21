{extends file="base/index"}

{block name="content_title" append}
    <div class="page-header">
        <h1><small>{translate key="label.unsplash"}</small></h1>
    </div>
{/block}

{block name="content_body" append}
    {include file="base/form.prototype"}
    <div class="grid">
        <div class="grid__2">
            <form class="form form-horizontal" action="{$app.url.request}" method="POST" role="form">
            <div class="form__group">
                {call formRows form=$form}
                <div class="form__actions">
                    <button type="submit" class="btn btn--default">{translate key="button.search"}</button>
                    {if isset($referer)}
                        <a href="{$referer}" class="btn btn--link">{translate key="button.cancel"}</a>
                    {/if}
                </div>
            </div>
            </form>
        </div>
    </div>
{/block}
