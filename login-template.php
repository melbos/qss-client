<?php 

/* Template Name: Login Template */

require_once 'qss-client.php';

session_start();

if ( ! empty( $_SESSION['qss_access_token'] ) ) {
    wp_redirect( home_url() );
    exit;
}

if ( isset( $_POST['qss_email'] ) && isset( $_POST['qss_password'] ) ) {
    $client = new QSS_Client( $_POST['qss_email'], $_POST['qss_password'] );

    try {
		
        $token = $client->get( '/token' )->token;
        $_SESSION['qss_access_token'] = $token;
        wp_redirect( home_url() );
        exit;
		
    } catch ( Exception $e ) {

        $error = $e->getMessage();
    }
}

get_header();
?>

<main>
    <h1>Login</h1>

    <?php if ( isset( $error ) ) : ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="qss_email">Email:</label>
        <input type="email" name="qss_email" id="qss_email" required>

        <label for="qss_password">Password:</label>
        <input type="password" name="qss_password" id="qss_password" required>

        <button type="submit">Login</button>
    </form>
</main>

<?php get_footer(); ?>