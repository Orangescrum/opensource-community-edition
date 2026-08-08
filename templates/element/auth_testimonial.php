<?php
/**
 * Auth Testimonial Element
 * Dynamic testimonial component for authentication pages
 * 
 * @var array $testimonial Contains: quote, author_name, author_role, author_image
 */

// Default testimonials if none provided
$defaultTestimonials = [
    'signup' => [
        'quote' => 'Orangescrum is the brain of our operations. It keeps everyone in sync and helps us deliver 25% faster.',
        'author_name' => 'Rehan L.',
        'author_role' => 'Associate Software Engineer',
        'author_image' => $this->Url->webroot('images/user-1.webp')
    ],
    'login' => [
        'quote' => 'I can manage all the tasks of my company in an organized way with this software.',
        'author_name' => 'Elizabeth L.',
        'author_role' => 'Sales And Marketing Specialist',
        'author_image' => $this->Url->webroot('images/user-2.webp')
    ],
    'forgot_password' => [
        'quote' => 'The password reset process is seamless. I was back in my account within minutes!',
        'author_name' => 'Kanishka',
        'author_role' => 'Business solution executive',
        'author_image' => $this->Url->webroot('images/user-3.jpeg')
    ]
];

// Use provided testimonial or default based on page context
if (!isset($testimonial)) {
    $page = isset($page) ? $page : 'signup';
    $testimonial = isset($defaultTestimonials[$page]) ? $defaultTestimonials[$page] : $defaultTestimonials['signup'];
}

// Extract testimonial data
$quote = $testimonial['quote'] ?? 'Orangescrum helps teams stay organized and productive.';
$authorName = $testimonial['author_name'] ?? 'Anonymous';
$authorRole = $testimonial['author_role'] ?? 'User';
$authorImage = $testimonial['author_image'] ?? 'https://i.pravatar.cc/100?u=default';
$rating = $testimonial['rating'] ?? 5; // Default 5 stars
?>

<!-- Testimonial -->
<div class="testimonial-card">
    <div class="stars">
        <?php for ($i = 0; $i < $rating; $i++): ?>
            <span class="glyphicon glyphicon-star"></span>
        <?php endfor; ?>
    </div>
    <p class="testimonial-text">
        "<?= h($quote) ?>"
    </p>
    <div class="author-info">
        <img src="<?= h($authorImage) ?>" class="avatar" alt="<?= h($authorName) ?>">
        <div>
            <span class="author-name"><?= h($authorName) ?></span>
            <span class="author-role"><?= h($authorRole) ?></span>
        </div>
    </div>
</div>
