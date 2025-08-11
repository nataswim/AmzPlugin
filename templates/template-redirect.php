<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@100;400;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <style>
        body {
            background: #1a1a2e;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Raleway', sans-serif;
            color: #e94560;
        }
        .container {
            text-align: center;
            background: #16213e;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 90%;
            width: 400px;
        }
        h1 {
            font-weight: 100;
            font-size: 2.5em;
            margin-bottom: 1rem;
        }
        .redirect-container {
            margin: 2rem 0;
        }
        .loader {
            width: 100px;
            height: 100px;
            border: 5px solid #0f3460;
            border-top: 5px solid #e94560;
            border-radius: 50%;
            margin: 20px auto;
        }
        .countdown {
            font-size: 1.2em;
            margin-top: 1rem;
        }
        #countdown {
            font-weight: bold;
            color: #0f3460;
        }
        a {
            color: #e94560;
            text-decoration: none;
            border-bottom: 1px dashed #e94560;
            transition: all 0.3s ease;
        }
        a:hover {
            color: #0f3460;
            border-bottom-color: #0f3460;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo esc_html__( 'Just a moment...', 'ams-wc-amazon' ); ?></h1>
        <div class="redirect-container">
            <div class="loader" id="loader"></div>
            <div><?php print esc_html( get_option( 'ams_checkout_mass_redirected' ) ?? '' ); ?></div>
        </div>
        <p class="countdown">
            <?php
            /* translators: %s: number of seconds */
            echo sprintf(
                esc_html__( "We're redirecting you in %s seconds...", 'ams-wc-amazon' ),
                '<span id="countdown">' . esc_html( $interval ) . '</span>'
            );
            ?>
        </p>
        <p>
            <?php
            /* translators: link text */
            printf(
                wp_kses(
                    __( 'Not working? <a href="%s">Click here.</a>', 'ams-wc-amazon' ),
                    [ 'a' => [ 'href' => [] ] ]
                ),
                esc_url( $redirect_url )
            );
            ?>
        </p>
    </div>
    <script>
        // Animation
        gsap.to("#loader", {rotation: 360, duration: 2, repeat: -1, ease: "linear"});

        // Countdown and redirect
        const countdownElement = document.getElementById('countdown');
        let countdown = <?php echo $interval; ?>;
        
        const countdownInterval = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.href = '<?php echo $redirect_url; ?>';
            }
        }, 1000);
    </script>
</body>
</html>