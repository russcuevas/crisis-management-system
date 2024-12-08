    <!-- Top Bar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
                <a href="javascript:void(0);" class="bars"></a>
                <a id="app-title" style="display:flex;align-items:center" class="navbar-brand" href="dashboard.php">
                    <img id="bcas-logo" style="width:45px;display:inline;margin-right:10px; border-radius: 50px;" src="images/admin/crisis.jpg" />
                    <span>CRISIS MANAGEMENT SYSTEM</span>
                </a>
            </div>
            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <!-- Notifications -->
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
                            <i class="material-icons">notifications</i>
                            <span class="label-count"><?php echo $unread_count; ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">NOTIFICATIONS</li>
                            <li class="body">
                                <ul class="menu">
                                    <?php if (empty($notifications_bells)): ?>
                                        <li>
                                            <div class="menu-info" style="margin-bottom: 20px; color: white;">
                                                <h3 style="padding: 50px; background-color: black;">No notification</h3>
                                            </div>
                                        </li>
                                    <?php else: ?>
                                        <?php foreach ($notifications_bells as $notifications_bell): ?>
                                            <?php if ($notifications_bell['incident_status'] === 'Pending'): ?>
                                                <li>
                                                    <a href="mark_as_viewed.php?incident_id=<?php echo $notifications_bell['incident_id']; ?>">
                                                        <div class="icon-circle bg-light-green">
                                                            <i class="material-icons">pending</i>
                                                        </div>
                                                        <div class="menu-info">
                                                            <strong style="font-size: 10px"><?php echo htmlspecialchars($notifications_bell['notification_description']); ?></strong>
                                                            <h4><?php echo htmlspecialchars($notifications_bell['incident_type']); ?></h4>
                                                            <p>
                                                                <i class="material-icons">access_time</i>
                                                                <span class="time-ago" data-time="<?php echo $notifications_bell['notification_created_at']; ?>">
                                                                    <?php echo timeAgo($notifications_bell['notification_created_at']); ?>
                                                                </span>
                                                            </p>
                                                        </div>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>


                                </ul>
                            </li>
                            <li class="footer">
                                <a href="view_all_notification.php">View All Notifications</a>
                            </li>
                        </ul>
                    </li>
                    <!-- #END# Notifications -->
                    <li class="pull-right"><a href="javascript:void(0);" class="js-right-sidebar" data-close="true"><i class="material-icons">account_circle</i></a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- #Top Bar -->


    <!-- INCREMENT TIME -->
    <script>
        function incrementTimeAgo() {
            const elements = document.querySelectorAll('.time-ago');

            elements.forEach(function(element) {
                const createdAt = new Date(element.getAttribute('data-time'));
                const now = new Date();

                const diffInSeconds = Math.floor((now - createdAt) / 1000);
                const minutes = Math.floor(diffInSeconds / 60);
                const hours = Math.floor(diffInSeconds / 3600);
                const days = Math.floor(diffInSeconds / (3600 * 24));
                const months = Math.floor(diffInSeconds / (3600 * 24 * 30));
                const years = Math.floor(diffInSeconds / (3600 * 24 * 365));

                let timeAgo = '';

                if (years > 0) {
                    timeAgo = years + " year" + (years > 1 ? "s" : "") + " ago";
                } else if (months > 0) {
                    timeAgo = months + " month" + (months > 1 ? "s" : "") + " ago";
                } else if (days > 0) {
                    timeAgo = days + " day" + (days > 1 ? "s" : "") + " ago";
                } else if (hours > 0) {
                    timeAgo = hours + " hour" + (hours > 1 ? "s" : "") + " ago";
                } else if (minutes > 0) {
                    timeAgo = minutes + " minute" + (minutes > 1 ? "s" : "") + " ago";
                } else {
                    timeAgo = "Just now";
                }
                element.innerText = timeAgo;
            });
        }

        incrementTimeAgo();
        setInterval(incrementTimeAgo, 60000);
    </script>