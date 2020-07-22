<div id="auth">

    <div class="container">

<?php if ( isset( $_GET['password'] ) ): ?>
        <p>Wrong password buddy.</p>
<?php else: ?>
        <p>This app requires a password.</p>
<?php endif; ?>

        <form class="form" action="" method="get" role="form">
<?php if ( isset( $_GET['domain'] ) ): ?>
            <input type="hidden" name="domain" value="<?=$_GET['domain'];?>">
<?php endif; ?>
            <input type="text" name="password" placeholder="password" autofocus>
        </form>

    </div><!-- container -->

</div><!-- auth -->

