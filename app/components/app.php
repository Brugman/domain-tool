<?php

$results = get_results();

?>
<div id="app">

    <div class="container">

        <form class="form" action="" method="get" role="form">
            <input type="hidden" name="password" value="<?=app_password();?>">
            <input type="text" name="domain" value="<?=$results['domain'] ?? '';?>" placeholder="domain.com" <?=( $results ? '' : 'autofocus' );?>>
        </form>

<?php if ( $results ): ?>

        <div class="results">

            <div class="card card--whois">
                <div class="bg-icon"><?=include_svg( 'user-crown-light' );?></div>
                <h2>WHOIS</h2>
                <?php display_results_whois( $results['domain'] ); ?>
            </div>

            <div class="card card--ns">
                <div class="bg-icon"><?=include_svg( 'map-signs-light' );?></div>
                <h2>Nameservers</h2>
                <?php display_results_ns( $results['ns'] ); ?>
            </div>

            <div class="card card--mx">
                <div class="bg-icon"><?=include_svg( 'at-light' );?></div>
                <h2>Mailservers</h2>
                <?php display_results_mx( $results['mx'] ); ?>
            </div>

            <div class="card card--web">
                <div class="bg-icon"><?=include_svg( 'server-light' );?></div>
                <h2>Webserver</h2>
                <?php display_results_a( $results['a'] ); ?>
            </div>

            <div class="card card--ssl">
                <div class="bg-icon"><?=include_svg( 'lock-alt-light' );?></div>
                <h2>SSL</h2>
                <?php display_results_ssl( $results['ssl'] ); ?>
            </div>

            <div class="card card--http">
                <div class="bg-icon"><?=include_svg( 'globe-light' );?></div>
                <h2>HTTP (beta)</h2>
                <?php display_results_http( $results['http_version'] ); ?>
            </div>

            <div class="card card--php">
                <div class="bg-icon"><?=include_svg( 'php' );?></div>
                <h2>PHP</h2>
                <?php display_results_php( $results['php_version'] ); ?>
            </div>

            <div class="card card--ss">
                <div class="bg-icon"><?=include_svg( 'cog-light' );?></div>
                <h2>Server software</h2>
                <?php display_results_software( $results['server_software'] ); ?>
            </div>

            <div class="card card--cms">
                <div class="bg-icon"><?=include_svg( 'feather-alt-light' );?></div>
                <h2>CMS</h2>
                <?php display_results_cms( $results['cms'] ); ?>
            </div>

        </div><!-- results -->

<?php endif; // $results ?>

    </div><!-- container -->

</div><!-- app -->

