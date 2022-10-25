<?php

namespace App\Traits;

trait Notifications {
    /**
     * Show success notification
     *
     * @param string $success
     * @return string
     */
    public function showSuccess(string $success): string
    {
        return '<div class="alert alert-success" role="alert">' . $success . '</div>';
    }

    /**
     * Show error notification to user
     *
     * @param string $error
     * @return string
     */
    public function showDanger(string $error): string
    {
        return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
    }

    /**
     * Show notification to user
     *
     * @param string $notification
     * @return string
     */
    public function showNotification(string $notification): string
    {
        return '<div class="alert alert-info" role="alert">' . $notification . '</div>';
    }
}