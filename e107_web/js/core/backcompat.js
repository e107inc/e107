
/* Legacy Theme v1.x compat when bootstrap not in use */

// (function ($)


    $('.s-message a.close').on('click', function ()
    {

        $(this).parent('.s-message').hide('slow');
    }
    );

