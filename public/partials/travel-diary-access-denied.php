<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$trip_id   = isset( $trip_id ) ? $trip_id : get_the_ID();
$reason    = isset( $reason ) ? $reason : 'private';
$trip_title = $trip_id ? get_the_title( $trip_id ) : __( 'questo viaggio', 'travel-diary' );

$messages = array(
	'token'         => array(
		'icon'  => '🔗',
		'title' => __( 'Link non valido', 'travel-diary' ),
		'body'  => __( 'Il link che hai usato non è valido o non è corretto. Chiedi all\'autore di condividere nuovamente il link aggiornato.', 'travel-diary' ),
	),
	'token_expired' => array(
		'icon'  => '⏰',
		'title' => __( 'Link scaduto', 'travel-diary' ),
		'body'  => __( 'Il link che hai usato era temporaneo ed è scaduto. Contatta l\'autore per richiederne uno nuovo.', 'travel-diary' ),
	),
	'members'       => array(
		'icon'  => '👥',
		'title' => __( 'Accesso riservato', 'travel-diary' ),
		'body'  => __( 'Questo viaggio è visibile solo agli utenti registrati. Accedi o registrati per continuare.', 'travel-diary' ),
	),
	'private'       => array(
		'icon'  => '🔒',
		'title' => __( 'Viaggio Privato', 'travel-diary' ),
		'body'  => __( 'Questo viaggio è privato e non è accessibile al pubblico.', 'travel-diary' ),
	),
);

$msg = $messages[ $reason ] ?? $messages['private'];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $msg['title'] ); ?> — <?php bloginfo( 'name' ); ?></title>
	<meta name="robots" content="noindex, nofollow">
	<?php wp_head(); ?>
	<style>
		.td-denied-wrap {
			min-height: 70vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 40px 20px;
			text-align: center;
		}
		.td-denied-box {
			max-width: 480px;
			padding: 48px 40px;
			background: #252525;
			border: 1px solid #333;
			border-radius: 12px;
		}
		.td-denied-icon { font-size: 3.5rem; margin-bottom: 16px; }
		.td-denied-title { font-size: 1.6rem; margin-bottom: 12px; color: #f5f0e8; }
		.td-denied-body  { color: #888; font-size: .95rem; line-height: 1.65; margin-bottom: 28px; }
		.td-denied-btn {
			display: inline-block;
			padding: 10px 24px;
			background: #d4943a;
			color: #1a1a1a;
			border-radius: 6px;
			font-weight: 600;
			font-size: .88rem;
			text-decoration: none;
			margin: 0 6px 8px;
			transition: background .2s;
		}
		.td-denied-btn:hover { background: #e8a84a; color: #1a1a1a; }
		.td-denied-btn--outline {
			background: transparent;
			color: #d4943a;
			border: 1.5px solid #d4943a;
		}
		.td-denied-btn--outline:hover { background: #d4943a; color: #1a1a1a; }
	</style>
</head>
<body <?php body_class( 'td-access-denied' ); ?>>
<?php wp_body_open(); ?>

<?php get_header(); ?>

<div class="td-denied-wrap">
	<div class="td-denied-box">
		<div class="td-denied-icon"><?php echo $msg['icon']; ?></div>
		<h1 class="td-denied-title"><?php echo esc_html( $msg['title'] ); ?></h1>
		<p class="td-denied-body"><?php echo esc_html( $msg['body'] ); ?></p>

		<div>
			<?php if ( $reason === 'members' && ! is_user_logged_in() ) : ?>
				<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="td-denied-btn">
					🔑 <?php _e( 'Accedi', 'travel-diary' ); ?>
				</a>
				<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="td-denied-btn td-denied-btn--outline">
					✍️ <?php _e( 'Registrati', 'travel-diary' ); ?>
				</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="td-denied-btn td-denied-btn--outline">
				🏠 <?php _e( 'Torna alla Home', 'travel-diary' ); ?>
			</a>
		</div>
	</div>
</div>

<?php get_footer(); ?>
<?php wp_footer(); ?>
</body>
</html>
