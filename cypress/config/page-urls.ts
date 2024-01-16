export const frontendUrls = {
    STUDENT_REGISTRATION: '/student-registration/',
    INSTRUCTOR_REGISTRATION: '/instructor-registration/',
    COURSES: '/courses',
    dashboard: {
        DASHBOARD: '/dashboard',
        MY_PROFILE: '/dashboard/my-profile/',
        ENROLLED_COURSES: '/dashboard/enrolled-courses/',
        WISHLIST: '/dashboard/wishlist',
        REVIEWS: '/dashboard/reviews/',
        MY_QUIZ_ATTEMPTS: '/dashboard/my-quiz-attempts/',
        ORDER_HISTORY: '/dashboard/purchase_history/',
        QUESTION_AND_ANSWER: '/dashboard/question-answer/',
        CALENDER: '/dashboard/calendar/',
        MY_COURSES: '/dashboard/my-courses/',
        MY_BUNDLES: '/dashboard/my-bundles/',
        ANNOUNCEMENTS: '/dashboard/announcements/',
        WITHDRAWS: '/dashboard/withdraw/',
        QUIZ_ATTEMPTS: '/dashboard/quiz-attempts/',
        GOOGLE_MEET: '/dashboard/google-meet/',
        ASSIGNMENTS: '/dashboard/assignments/',
        ZOOM: '/dashboard/zoom/',
        CERTIFICATE: '/dashboard/certificate-builder/',
        ANALYTICS: '/dashboard/analytics/',
        SETTINGS: '/dashboard/settings/',
        LOGOUT: '/dashboard/logout',
    }
}

export const backendUrls = {
    LOGIN: 'wp-login.php',
    COURSE_BUNDLES: 'wp-admin/admin.php?page=course-bundle',
    CATEGORIES: 'wp-admin/edit-tags.php?taxonomy=course-category&post_type=courses',
    TAGS: 'wp-admin/edit-tags.php?taxonomy=course-tag&post_type=courses',
    INSTRUCTORS: 'wp-admin/admin.php?page=tutor-instructors',
    ANNOUNCEMENTS: 'wp-admin/admin.php?page=tutor_announcements',
    QUESTION_AND_ANSWER: 'wp-admin/admin.php?page=question_answer',
    QUIZ_ATTEMPTS: 'wp-admin/admin.php?page=tutor_quiz_attempts',
}
