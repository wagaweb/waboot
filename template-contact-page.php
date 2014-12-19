<?php
/*
/* Template DISABLED Name: Contact Page
*/
?>

<?php
$error = '';
$success = '';
if( isset($_POST['action']) && $_POST['action']=='contact-form' )
{
    $name = $_POST['message_name'];
    $email = $_POST['message_email'];
    $message = $_POST['message_text'];
    if( $name == "" || $email == "" || $message == "" ) {
        $error = 'Fields are required.';
    } else if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
        $error = 'Invalid email address.';
    } else {
        /* get admin email from database */
        $to = get_option('admin_email');
        $headers = 'From: "'. $name .'" <' . $email . '>';
        $mail = wp_mail( $to, $message, $headers);
        if( $mail ) {
            $success = 'Message successfully sent.';
        }
    }
}
?>

<?php get_header(); ?>

    <div id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'content-area col-sm-8' ); ?>">
        <main id="main" class="site-main" role="main">

            <?php the_content(); ?>

            <div id="waboot-contact-form">
                <div class="message-box">
                    <?php if( $error != "" ) { echo '<div class="alert alert-danger error-box">'.$error.'</div>'; } ?>
                    <?php if( $success != "" ) { echo '<div class="alert alert-success success-box">'.$success.'</div>'; } ?>
                </div>

                <form action="<?php the_permalink(); ?>" method="post" class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="message_name" class="col-sm-2 control-label">Name: <span>*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="message_name" id="message_name" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="message_email" class="col-sm-2 control-label">Email: <span>*</span></label>
                        <div class="col-sm-10">
                            <input type="email" class="form-control" name="message_email" id="message_email" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name" class="col-sm-2 control-label">Message: <span>*</span></label>
                        <div class="col-sm-10">
                            <textarea type="text" class="form-control" name="message_text" id="message_text"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-2 control-label"></div>
                        <div class="col-sm-10">
                            <input type="submit" class="btn btn-primary" value="Send">
                            <input type="hidden" name="action" value="contact-form">
                        </div>
                    </div>
                </form>
            </div>

        </main><!-- #main -->
    </div><!-- #primary -->
<?php
get_sidebar();
get_footer();