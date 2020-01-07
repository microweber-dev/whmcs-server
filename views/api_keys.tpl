<form action="index.php?m=microweber_server&action=generate_api_keys" method="post" id="" class="form-horizontal">

    <div class="well">
       <b>Api Key</b>
        <br />
        <br />

        {if $api_key}
            Key:
            <br />
            <pre>{$api_key}</pre>
            Expiration date: {$api_key_expiration_date}
        {else}
            No api key
        {/if}
    </div>

    <button type="submit" class="btn btn-primary">
        Generate Key
    </button>

</form>