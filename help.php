<?php
require_once 'common/config.php';

$settings = getSettings($conn);

include 'common/header.php';
include 'common/sidebar.php';
?>

<!-- Page Title -->
<div class="p-4 bg-white border-b">
    <h1 class="text-xl font-bold text-gray-800">Help & Support</h1>
</div>

<div class="p-4">
    <!-- Contact Info -->
    <div class="bg-white p-6 mb-4">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-sky-100 text-sky-600 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-headset text-3xl"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-800">Need Help?</h2>
            <p class="text-gray-600 mt-1">We're here to assist you</p>
        </div>
        
        <div class="space-y-4">
            <a href="mailto:<?php echo $settings['support_email']; ?>" class="flex items-center gap-4 p-4 bg-gray-50">
                <div class="w-12 h-12 bg-sky-600 text-white flex items-center justify-center">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email Us</p>
                    <p class="font-semibold text-gray-800"><?php echo $settings['support_email']; ?></p>
                </div>
            </a>
            
            <a href="tel:<?php echo $settings['support_phone']; ?>" class="flex items-center gap-4 p-4 bg-gray-50">
                <div class="w-12 h-12 bg-green-500 text-white flex items-center justify-center">
                    <i class="fas fa-phone"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Call Us</p>
                    <p class="font-semibold text-gray-800"><?php echo $settings['support_phone']; ?></p>
                </div>
            </a>
        </div>
    </div>
    
    <!-- FAQs -->
    <div class="bg-white p-4">
        <h3 class="font-semibold text-gray-800 mb-4">Frequently Asked Questions</h3>
        
        <div class="space-y-3">
            <div class="border-b pb-3">
                <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full flex items-center justify-between text-left font-medium text-gray-800">
                    How do I access my purchased courses?
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </button>
                <p class="hidden mt-2 text-gray-600 text-sm">After purchasing a course, go to "My Courses" from the bottom navigation. All your purchased courses will be listed there.</p>
            </div>
            
            <div class="border-b pb-3">
                <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full flex items-center justify-between text-left font-medium text-gray-800">
                    Can I download videos for offline viewing?
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </button>
                <p class="hidden mt-2 text-gray-600 text-sm">Currently, videos can only be streamed online. Offline download feature is not available.</p>
            </div>
            
            <div class="border-b pb-3">
                <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full flex items-center justify-between text-left font-medium text-gray-800">
                    What payment methods are accepted?
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </button>
                <p class="hidden mt-2 text-gray-600 text-sm">We accept all major payment methods through Razorpay including UPI, Credit/Debit Cards, Net Banking, and Wallets.</p>
            </div>
            
            <div class="pb-3">
                <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full flex items-center justify-between text-left font-medium text-gray-800">
                    Is there a refund policy?
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </button>
                <p class="hidden mt-2 text-gray-600 text-sm">Please contact our support team for refund-related queries. Each case is reviewed individually.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
