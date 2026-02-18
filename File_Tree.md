# File Tree: website_ujian

**Generated:** 2/15/2026, 7:12:18 AM
**Root Path:** `website_ujian`

```
├── 📁 app
│   ├── 📁 Console
│   │   └── 📁 Commands
│   │       ├── 🐘 ExpireActiveAttempts.php
│   │       └── 🐘 FinishExpiredExams.php
│   ├── 📁 Events
│   ├── 📁 Exceptions
│   │   └── 🐘 StateConflictException.php
│   ├── 📁 Http
│   │   ├── 📁 Controllers
│   │   │   ├── 📁 Admin
│   │   │   │   ├── 🐘 AdminExamController.php
│   │   │   │   └── 🐘 AdminUserController.php
│   │   │   ├── 📁 Auth
│   │   │   │   ├── 🐘 AuthenticatedSessionController.php
│   │   │   │   ├── 🐘 ConfirmablePasswordController.php
│   │   │   │   ├── 🐘 EmailVerificationNotificationController.php
│   │   │   │   ├── 🐘 EmailVerificationPromptController.php
│   │   │   │   ├── 🐘 NewPasswordController.php
│   │   │   │   ├── 🐘 PasswordController.php
│   │   │   │   ├── 🐘 PasswordResetLinkController.php
│   │   │   │   ├── 🐘 RegisteredUserController.php
│   │   │   │   └── 🐘 VerifyEmailController.php
│   │   │   ├── 📁 Operator
│   │   │   │   └── 🐘 OperatorExamMonitorController.php
│   │   │   ├── 📁 Peserta
│   │   │   │   └── 🐘 PesertaExamController.php
│   │   │   ├── 🐘 Controller.php
│   │   │   ├── 🐘 DashboardController.php
│   │   │   └── 🐘 ProfileController.php
│   │   ├── 📁 Middleware
│   │   │   ├── 🐘 EnsureManualScoreIntent.php
│   │   │   ├── 🐘 EnsureUserIsAdmin.php
│   │   │   ├── 🐘 EnsureUserIsOperator.php
│   │   │   └── 🐘 EnsureUserIsPeserta.php
│   │   └── 📁 Requests
│   │       ├── 📁 Auth
│   │       │   └── 🐘 LoginRequest.php
│   │       └── 🐘 ProfileUpdateRequest.php
│   ├── 📁 Listeners
│   ├── 📁 Models
│   │   ├── 🐘 Exam.php
│   │   ├── 🐘 ExamAnswer.php
│   │   ├── 🐘 ExamAttempt.php
│   │   ├── 🐘 ExamAttemptAudit.php
│   │   ├── 🐘 ExamOption.php
│   │   ├── 🐘 ExamQuestion.php
│   │   └── 🐘 User.php
│   ├── 📁 Policies
│   │   ├── 🐘 ExamAttemptPolicy.php
│   │   └── 🐘 ExamPolicy.php
│   ├── 📁 Providers
│   │   ├── 🐘 AppServiceProvider.php
│   │   └── 🐘 EventServiceProvider.php
│   ├── 📁 Services
│   │   ├── 🐘 ExamLifecycleService.php
│   │   ├── 🐘 ExamParticipationService.php
│   │   ├── 🐘 ExamScoringService.php
│   │   └── 🐘 OperatorExamService.php
│   ├── 📁 Support
│   │   ├── 🐘 ExamUiAction.php
│   │   └── 🐘 ExamUiState.php
│   ├── 📁 View
│   │   └── 📁 Components
│   │       ├── 🐘 AppLayout.php
│   │       ├── 🐘 GuestLayout.php
│   │       └── 🐘 layouts.php
│   └── 🐘 helpers.php
├── 📁 bootstrap
│   ├── 🐘 app.php
│   └── 🐘 providers.php
├── 📁 config
│   ├── 🐘 app.php
│   ├── 🐘 auth.php
│   ├── 🐘 cache.php
│   ├── 🐘 database.php
│   ├── 🐘 filesystems.php
│   ├── 🐘 logging.php
│   ├── 🐘 mail.php
│   ├── 🐘 queue.php
│   ├── 🐘 services.php
│   ├── 🐘 session.php
│   └── 🐘 sidebar.php
├── 📁 database
│   ├── 📁 factories
│   │   └── 🐘 UserFactory.php
│   ├── 📁 migrations
│   │   ├── 🐘 0001_01_01_000000_create_users_table.php
│   │   ├── 🐘 0001_01_01_000001_create_cache_table.php
│   │   ├── 🐘 0001_01_01_000002_create_jobs_table.php
│   │   ├── 🐘 2026_01_07_074652_create_matches_table.php
│   │   ├── 🐘 2026_01_08_074600_create_teams_table.php
│   │   ├── 🐘 2026_01_08_074737_create_questions_table.php
│   │   ├── 🐘 2026_01_08_074836_create_match_questions_table.php
│   │   ├── 🐘 2026_01_08_074934_create_buzzes_table.php
│   │   ├── 🐘 2026_01_08_075020_create_answers_table.php
│   │   ├── 🐘 2026_02_13_000001_add_role_to_users_table.php
│   │   ├── 🐘 2026_02_13_000002_update_user_role_to_peserta.php
│   │   ├── 🐘 2026_02_13_000003_create_exams_table.php
│   │   ├── 🐘 2026_02_13_000004_create_exam_questions_table.php
│   │   ├── 🐘 2026_02_13_000005_create_exam_options_table.php
│   │   ├── 🐘 2026_02_13_000006_create_exam_attempts_table.php
│   │   ├── 🐘 2026_02_13_000007_create_exam_answers_table.php
│   │   ├── 🐘 2026_02_13_000008_add_profile_photo_path_to_users_table.php
│   │   ├── 🐘 2026_02_13_000009_enforce_state_machine_statuses.php
│   │   ├── 🐘 2026_02_14_000010_create_exam_attempt_audits_table.php
│   │   ├── 🐘 2026_02_14_000011_normalize_exam_attempt_status_for_sqlite.php
│   │   └── 🐘 2026_02_14_000012_drop_legacy_match_tables.php
│   ├── 📁 seeders
│   │   ├── 🐘 DatabaseSeeder.php
│   │   └── 🐘 ExamSeeder.php
│   ├── ⚙️ .gitignore
│   └── 📄 database.sqlite
├── 📁 docs
│   ├── 📁 adr
│   │   ├── 📝 ADR-001-exam-status-mutation-via-lifecycle-service-only.md
│   │   ├── 📝 ADR-002-domain-centric-exam-test-suite.md
│   │   ├── 📝 ADR-003-backend-owned-ui-state-contract.md
│   │   └── 📝 README.md
│   ├── 📝 api.md
│   ├── 📝 architecture.md
│   ├── 📝 changelog-architecture.md
│   ├── 📝 deployment.md
│   ├── 📝 quickstart-role-playbook.md
│   ├── 📝 runbook.md
│   ├── 📝 sequence.md
│   ├── 📝 state-machine.md
│   └── 📝 testing.md
├── 📁 public
│   ├── ⚙️ .htaccess
│   ├── 📄 favicon.ico
│   ├── 🐘 index.php
│   └── 📄 robots.txt
├── 📁 resources
│   ├── 📁 css
│   │   └── 🎨 app.css
│   ├── 📁 js
│   │   ├── 📄 app.js
│   │   ├── 📄 bootstrap.js
│   │   └── 📄 layout.js
│   └── 📁 views
│       ├── 📁 admin
│       │   ├── 📁 exams
│       │   │   ├── 🐘 index.blade.php
│       │   │   └── 🐘 show.blade.php
│       │   └── 📁 users
│       │       └── 🐘 index.blade.php
│       ├── 📁 auth
│       │   ├── 🐘 confirm-password.blade.php
│       │   ├── 🐘 forgot-password.blade.php
│       │   ├── 🐘 login.blade.php
│       │   ├── 🐘 register.blade.php
│       │   ├── 🐘 reset-password.blade.php
│       │   └── 🐘 verify-email.blade.php
│       ├── 📁 components
│       │   ├── 🐘 application-logo.blade.php
│       │   ├── 🐘 auth-session-status.blade.php
│       │   ├── 🐘 danger-button.blade.php
│       │   ├── 🐘 dropdown-link.blade.php
│       │   ├── 🐘 dropdown.blade.php
│       │   ├── 🐘 icon.blade.php
│       │   ├── 🐘 input-error.blade.php
│       │   ├── 🐘 input-label.blade.php
│       │   ├── 🐘 layouts.blade.php
│       │   ├── 🐘 modal.blade.php
│       │   ├── 🐘 nav-link.blade.php
│       │   ├── 🐘 primary-button.blade.php
│       │   ├── 🐘 responsive-nav-link.blade.php
│       │   ├── 🐘 secondary-button.blade.php
│       │   ├── 🐘 sidebar-link.blade.php
│       │   ├── 🐘 sidebar.blade.php
│       │   └── 🐘 text-input.blade.php
│       ├── 📁 dashboard
│       │   ├── 🐘 role-admin.blade.php
│       │   ├── 🐘 role-operator.blade.php
│       │   └── 🐘 role-peserta.blade.php
│       ├── 📁 errors
│       │   ├── 📁 partials
│       │   │   └── 🐘 themed.blade.php
│       │   ├── 🐘 401.blade.php
│       │   ├── 🐘 403.blade.php
│       │   ├── 🐘 404.blade.php
│       │   ├── 🐘 419.blade.php
│       │   ├── 🐘 429.blade.php
│       │   ├── 🐘 500.blade.php
│       │   └── 🐘 503.blade.php
│       ├── 📁 judge
│       ├── 📁 layouts
│       │   ├── 🐘 app.blade.php
│       │   ├── 🐘 guest.blade.php
│       │   └── 🐘 navigation.blade.php
│       ├── 📁 operator
│       │   └── 📁 exams
│       │       ├── 🐘 index.blade.php
│       │       └── 🐘 show.blade.php
│       ├── 📁 peserta
│       │   └── 📁 exams
│       │       ├── 🐘 index.blade.php
│       │       ├── 🐘 result.blade.php
│       │       └── 🐘 show.blade.php
│       ├── 📁 profile
│       │   ├── 📁 partials
│       │   │   ├── 🐘 delete-user-form.blade.php
│       │   │   ├── 🐘 update-password-form.blade.php
│       │   │   └── 🐘 update-profile-information-form.blade.php
│       │   └── 🐘 edit.blade.php
│       ├── 🐘 dashboard.blade.php
│       └── 🐘 welcome.blade.php
├── 📁 routes
│   ├── 🐘 auth.php
│   ├── 🐘 channels.php
│   ├── 🐘 console.php
│   └── 🐘 web.php
├── 📁 storage
│   ├── 📁 app
│   │   ├── 📁 private
│   │   │   └── ⚙️ .gitignore
│   │   ├── 📁 public
│   │   │   ├── 📁 profile-photos
│   │   │   │   └── 🖼️ N6uZhzgENVbvFmbN2JTlq936icT7mdSdzYSoI5dK.png
│   │   │   └── ⚙️ .gitignore
│   │   └── ⚙️ .gitignore
│   ├── 📁 framework
│   │   ├── 📁 sessions
│   │   │   └── ⚙️ .gitignore
│   │   ├── 📁 testing
│   │   │   └── ⚙️ .gitignore
│   │   ├── 📁 views
│   │   │   ├── ⚙️ .gitignore
│   │   │   ├── 🐘 013cd546c9e32841862e10ec049babf0.php
│   │   │   ├── 🐘 01bad4d50b6118e220d825023760fa05.php
│   │   │   ├── 🐘 06fc31d847cd396089557262bd3c6f74.php
│   │   │   ├── 🐘 096fa0dd1bccbb38cca0a62ce5f76790.php
│   │   │   ├── 🐘 09ab3aa35100eed5c23fda18998faf0e.php
│   │   │   ├── 🐘 0c645bed7f7fe07a6ad3cc91572f7b83.php
│   │   │   ├── 🐘 0e64ae2be5535a81c6911ff86b2c87c9.php
│   │   │   ├── 🐘 0ed1d0dd37b0e9073261e1420ba88b5c.php
│   │   │   ├── 🐘 100893f44339e309bc3886d0e43cd991.php
│   │   │   ├── 🐘 11418e1b63dcf7622c088ee65609deb4.php
│   │   │   ├── 🐘 12c441647ef2fad159f0239e4fefd105.php
│   │   │   ├── 🐘 12ce9a39814ff8a1a4c1138ca83a7a9b.php
│   │   │   ├── 🐘 13cc60c25e522748f8f10cc92257d6ac.php
│   │   │   ├── 🐘 181c2e1a8788e43278022bea351e1856.php
│   │   │   ├── 🐘 1b059318c4433c650fcd5d1e2ac5bf85.php
│   │   │   ├── 🐘 1b53bfdd04549712a6e25a820d878fb0.php
│   │   │   ├── 🐘 1ccc1e6f608565863edeba94bb4e5c39.php
│   │   │   ├── 🐘 1d3fd24871bbccdd488ed565cb0eba44.php
│   │   │   ├── 🐘 1d990fbfd4ae7c85fa14f33a863aae9f.php
│   │   │   ├── 🐘 1ddc940fa8279373b2ce9bd4d1389c0b.php
│   │   │   ├── 🐘 1e75fdd8bef3c22a7f061798b7f98b9a.php
│   │   │   ├── 🐘 1fa8df7fc71f3b86ab3235a3039b3021.php
│   │   │   ├── 🐘 22c8c640eb9f02afd05cb94d38773fed.php
│   │   │   ├── 🐘 2642f1164c614bb217963a46655567a5.php
│   │   │   ├── 🐘 266ec6f4f563ad158de6ccf049750636.php
│   │   │   ├── 🐘 27765de8cae70d0d5443be55a35b3ee3.php
│   │   │   ├── 🐘 2cca373197c81826df427736f9141685.php
│   │   │   ├── 🐘 2d4a114fa0915fc82d18093058538dbc.php
│   │   │   ├── 🐘 2da33ffce4630e68f1f67f9ec23f73d6.php
│   │   │   ├── 🐘 2fb3513229ab10a90933e1894a018322.php
│   │   │   ├── 🐘 316cd6e5ff159906a6f00068e3c2cab1.php
│   │   │   ├── 🐘 31bb307fe3b30c7aa97f7cbb65ed4ee7.php
│   │   │   ├── 🐘 339fd8218b5a00a73f3aa4257e5fa6a0.php
│   │   │   ├── 🐘 3635437a015677ea1c6cfcb9a1be40f8.php
│   │   │   ├── 🐘 36fed4353278913d6fd90619704879e5.php
│   │   │   ├── 🐘 373dff41156be2074e528b6b9bbf19e0.php
│   │   │   ├── 🐘 3965795533b04007a7c0593d8e3cd6ea.php
│   │   │   ├── 🐘 39de5ad16ede097967b53c032d0db663.php
│   │   │   ├── 🐘 3b61e9119073d409ceb7efe020bcbcff.php
│   │   │   ├── 🐘 3dcec8dd54e5182c9b28499eaebf48a9.php
│   │   │   ├── 🐘 3e1ef3071ae09ba218de2ce3f7da901a.php
│   │   │   ├── 🐘 409c55351f59a520f28e6a978e927106.php
│   │   │   ├── 🐘 41ac47de0e6f8623de474b4cece5123a.php
│   │   │   ├── 🐘 4246df76ccf3eebb70cf85dfdf1aa20c.php
│   │   │   ├── 🐘 42f72446995d03e210bf4d469ef5cb61.php
│   │   │   ├── 🐘 463265ef9c8a0da5747067c5ae6a40c8.php
│   │   │   ├── 🐘 48262292cb1f950d406acbe863bdd0e8.php
│   │   │   ├── 🐘 49cf2eb70dbce8c3dfcf4d18de26c4ec.php
│   │   │   ├── 🐘 49f4af80bd43b640bbfc110fc68fb5ee.php
│   │   │   ├── 🐘 4cab1fe1f96eb8b3627b6e80ef8b6709.php
│   │   │   ├── 🐘 4ed5ffb2997bbbf43e412da5b0682407.php
│   │   │   ├── 🐘 51588dc4bffed6fa0bdd3ab02b239e70.php
│   │   │   ├── 🐘 53ae054c6cf8895d92584968abb5d897.php
│   │   │   ├── 🐘 5414d33b911d26b3050abd2e20dc4c65.php
│   │   │   ├── 🐘 571993a8debf7962ee95913a017d4f13.php
│   │   │   ├── 🐘 59dc51bf81a326105b8bc6cd7df8da7c.php
│   │   │   ├── 🐘 5b7cc2c662410e2b9bed17a57a2921ac.php
│   │   │   ├── 🐘 5e803112f3dc8e2333cf82e4a54188ac.php
│   │   │   ├── 🐘 5f6dfde687086dbe0f45784a5a98b2d1.php
│   │   │   ├── 🐘 602035ffa5f95534debd2b44e5b43429.php
│   │   │   ├── 🐘 60d31c0244dfadff62626dee866020a7.php
│   │   │   ├── 🐘 610215e15f5d7857778e006eec0a2f0e.php
│   │   │   ├── 🐘 61f7b0b7d31919c75790ec3dec83be97.php
│   │   │   ├── 🐘 6452b9c1bb82510b0a59d2ed20bde7ec.php
│   │   │   ├── 🐘 652ab44128f4fb6be213037f7301be45.php
│   │   │   ├── 🐘 65961d9a1d6191360ca092863f0babac.php
│   │   │   ├── 🐘 65e80002d80c3a91d478ce6495a3c236.php
│   │   │   ├── 🐘 662462ddffac7359f353d2d7aeab959d.php
│   │   │   ├── 🐘 66d60c3eedf41b30c79cd79096833f84.php
│   │   │   ├── 🐘 6791a6f880fb5ebcbc626c70e874b35e.php
│   │   │   ├── 🐘 69532bd6954babf691d3622ca60ee662.php
│   │   │   ├── 🐘 69d0d493a9e911663b37c4c2e40589f8.php
│   │   │   ├── 🐘 6c084654d45327a36bce2a9e261f71dd.php
│   │   │   ├── 🐘 6c26c71fa44da9f798964e5db0d56633.php
│   │   │   ├── 🐘 6cdd3f66780f353b08c48f2a809b58cb.php
│   │   │   ├── 🐘 6e28bd32ff4ad1b9f55869456bfc9b42.php
│   │   │   ├── 🐘 6fce0363424bb9fa3690ed6404dd2987.php
│   │   │   ├── 🐘 703d3a2875638d1d63fb8bab60384a90.php
│   │   │   ├── 🐘 70cc59f790e4869770b1353b15a1ed5f.php
│   │   │   ├── 🐘 72bb25a14dc8bf4ca1d287039fb11eed.php
│   │   │   ├── 🐘 753f28913a1f8c8a2442ba753013c3a4.php
│   │   │   ├── 🐘 78f0f608180148e58a0f76dcb98227b7.php
│   │   │   ├── 🐘 7d133eaa6a0dc026ddb075c22b03152c.php
│   │   │   ├── 🐘 82411070e7641256f2a01058b78f0a61.php
│   │   │   ├── 🐘 8289854325aeeee9d9198e4424fb05f8.php
│   │   │   ├── 🐘 84c7ccb05dc25de9ed03df1affddb4f7.php
│   │   │   ├── 🐘 85f991cc0502ee946ae76148ce009642.php
│   │   │   ├── 🐘 895d6eb853fad55e608d3d5d29d3df48.php
│   │   │   ├── 🐘 8cf0fcd5b542e467d0d97ce4e36862d0.php
│   │   │   ├── 🐘 8f1cfa27139a384042a691d3fa71e554.php
│   │   │   ├── 🐘 90faa8a08963b8d0d9f2687e36e2c62e.php
│   │   │   ├── 🐘 96d3739f1d56b403f9cd884988a64594.php
│   │   │   ├── 🐘 97c131f4e9eebcbecb8d0828a2f4fe83.php
│   │   │   ├── 🐘 97e2c33802763c5c6a4fcaf22ed0346a.php
│   │   │   ├── 🐘 9856699df3ab8665eb2af980f09703dd.php
│   │   │   ├── 🐘 98e8a2a3022831bd34de3d0074bbc8d6.php
│   │   │   ├── 🐘 9abb7d0c32ea507ae5bd4d66bd54ee58.php
│   │   │   ├── 🐘 9dca877ac184da612de163d9d4dbaf73.php
│   │   │   ├── 🐘 9e538f09bce7aba05b94ac2ca6136802.php
│   │   │   ├── 🐘 a21e1aa5d68414c28692fc86ee51bb9e.php
│   │   │   ├── 🐘 a271b5a46cd7d113176c7054a20c0003.php
│   │   │   ├── 🐘 a325a8414371af994a8dc5180bbaec06.php
│   │   │   ├── 🐘 a37e089e0421ed45f5d085f5760b92f1.php
│   │   │   ├── 🐘 a46840eec91a17abdab1641807f6899c.php
│   │   │   ├── 🐘 a6fdaffaef819ff2e884bbeda2d12f98.php
│   │   │   ├── 🐘 aa185162ed14ff9cad1171929552e3ec.php
│   │   │   ├── 🐘 aacb10c1050f496841beb4c0f1d1c87d.php
│   │   │   ├── 🐘 ab8a22b4542b37906206928cbb6af353.php
│   │   │   ├── 🐘 abf716bc26ef0c8f0afe638fbf973505.php
│   │   │   ├── 🐘 b140bbdbe137d14250773dd1dfbfb154.php
│   │   │   ├── 🐘 b1488fa92c9b0db01bcdce9c14a7848f.php
│   │   │   ├── 🐘 b15a1866179695e8595b34b0c917d702.php
│   │   │   ├── 🐘 b1c2a52f1833919956c49916b3367112.php
│   │   │   ├── 🐘 b239e95e8801de4cd0da3753bf3bee49.php
│   │   │   ├── 🐘 b403f04148e16e2c76200a3defd353b7.php
│   │   │   ├── 🐘 b4793cfd3e0263b825b715870118793b.php
│   │   │   ├── 🐘 b4d76344471a2730b7ef56204973e9ef.php
│   │   │   ├── 🐘 b5837abfbc98d540d7ba96b31f30ab76.php
│   │   │   ├── 🐘 b5896873b12c472ce39f209856e53e7e.php
│   │   │   ├── 🐘 b634751ad357305ddef5bcf01ef5a50d.php
│   │   │   ├── 🐘 b731601602795b21818f80f8f573dfab.php
│   │   │   ├── 🐘 b95d3a3b1649fb01c59edc1f226f1232.php
│   │   │   ├── 🐘 ba2d1ce02f921d219a976f8b26ffbfb0.php
│   │   │   ├── 🐘 bc54c123c08ceb405b99cbe0c43e235b.php
│   │   │   ├── 🐘 c0b41f57eaf28ffa7f73411f9c551d81.php
│   │   │   ├── 🐘 c181ab156d41812e185894babb2c2da0.php
│   │   │   ├── 🐘 c2e30c3f77fc022955612bf66b3dacdf.php
│   │   │   ├── 🐘 c695e1ce5c3cdaf775a6edfbf63813cc.php
│   │   │   ├── 🐘 c714891e463d2655abf7cf56ef8757bd.php
│   │   │   ├── 🐘 c79a00ddec5f6e3087389a67c3db553d.php
│   │   │   ├── 🐘 c7a7c99804133799107aa5a0170acd55.php
│   │   │   ├── 🐘 c7e5f4053bc8e8264f09cbe0663b0a8f.php
│   │   │   ├── 🐘 c9b7ac5917d616c1dba53c83343c2034.php
│   │   │   ├── 🐘 cbae889a1729b353c5a8f7abf27ff9a3.php
│   │   │   ├── 🐘 ce06f2348c3d913b1127e280ce2d15a2.php
│   │   │   ├── 🐘 cf40425e4e77427b40014a63ac4e2dc5.php
│   │   │   ├── 🐘 d05c374fa0ad96582bc4d61e7fce3a86.php
│   │   │   ├── 🐘 d07b7f46f4e0bf8d1935d365b5e16947.php
│   │   │   ├── 🐘 d241ec8a29e4ad7250c6e84f05b7cfc0.php
│   │   │   ├── 🐘 d3b9741e0f26fdca4b56432f3c2e3492.php
│   │   │   ├── 🐘 d493c05e51415e9b6fd75a564155be56.php
│   │   │   ├── 🐘 d4dbeed5b86e090fba2bf47dc3f234e3.php
│   │   │   ├── 🐘 d5c52055840beb58142b1f501584013b.php
│   │   │   ├── 🐘 d698b77990ef8474f855902c5f7c4e79.php
│   │   │   ├── 🐘 d6a198a478bdefbd22ce8f4d929cc998.php
│   │   │   ├── 🐘 d6a999c199ec2fbad23e43033d5f02f4.php
│   │   │   ├── 🐘 d7b229f840975161ab26ba0a65910dbc.php
│   │   │   ├── 🐘 d8225a5d3e7369994d7899652a5777f4.php
│   │   │   ├── 🐘 d9dd97749b34303eb137b933da758f8b.php
│   │   │   ├── 🐘 d9deabf125995b0298b1847284418831.php
│   │   │   ├── 🐘 da7d8d675b47d4e26d2600b23542e206.php
│   │   │   ├── 🐘 db2e32dee32f5d5e0a69aacde2d545a6.php
│   │   │   ├── 🐘 db5cbfb42c888eaeffdf62c05b358a98.php
│   │   │   ├── 🐘 dc6a4aa48f14a6c3aeb67ec26800ba6a.php
│   │   │   ├── 🐘 dc6c876a6aee9e805a5473f66aab9799.php
│   │   │   ├── 🐘 dca32461033d15ee0ec3fd49f1344cae.php
│   │   │   ├── 🐘 ddccbecb2131fc8e4b9e5a6b216ce970.php
│   │   │   ├── 🐘 de00811eb0d60b5748d3f14c06c52d95.php
│   │   │   ├── 🐘 dfdb03d1917faa469a2f5bb99cd04a25.php
│   │   │   ├── 🐘 e0bbfcba12956c1763ce3564acfc497c.php
│   │   │   ├── 🐘 e1e8f56025e9234002004566baff62c4.php
│   │   │   ├── 🐘 e38be50172e36aec13e47d4803f8caa8.php
│   │   │   ├── 🐘 e3f0853e2059ffe4d564ae9d657d31d6.php
│   │   │   ├── 🐘 e4026c13276fdb5bb62952c96804a4d6.php
│   │   │   ├── 🐘 e435e20e49208d6cf66c0e926fc5dbb4.php
│   │   │   ├── 🐘 e4d34a83bde941eb65ff80230df3e3c2.php
│   │   │   ├── 🐘 e81a7258648c5826eb9c2814642f4236.php
│   │   │   ├── 🐘 e8d92bdc2c1993506cc7c95844c0136b.php
│   │   │   ├── 🐘 e8ec7f81f8d2c20be8f32a3a715449c4.php
│   │   │   ├── 🐘 e9c93fdf3f65c7d8935400517ba413aa.php
│   │   │   ├── 🐘 ebe0b4a2f4533d3cd95f08dfc8a0160c.php
│   │   │   ├── 🐘 ed52fa6c907e056e4238dbaa241ec9d1.php
│   │   │   ├── 🐘 edffc400ffc2536cd2b1ae809de9e8eb.php
│   │   │   ├── 🐘 ef261b04f447e9b81f4c7d69274bab14.php
│   │   │   ├── 🐘 ef8bef7e9d7e78628b9d4a351fc89b80.php
│   │   │   ├── 🐘 f386041171cb4b0819eaa7c7b5927b7f.php
│   │   │   ├── 🐘 f8342d07d7a06b6f1cf7f0151f9bd9c5.php
│   │   │   ├── 🐘 f8a47bc7f904c9dbb73ca584bf2f609f.php
│   │   │   ├── 🐘 f8ac15122de1a8893618a7dcad237b77.php
│   │   │   ├── 🐘 f993f35fab5b53436667529a7de1b959.php
│   │   │   ├── 🐘 fb38420cdddd504f2d28b668f79632da.php
│   │   │   ├── 🐘 fc85070c7f3cf82f2cc336eb7f4d0c5e.php
│   │   │   ├── 🐘 fdc471dcaf5a5034a2873068588b4b95.php
│   │   │   └── 🐘 fe10f437fa356ce2d9ceefc8da7fb401.php
│   │   └── ⚙️ .gitignore
│   └── 📁 logs
│       └── ⚙️ .gitignore
├── 📁 tests
│   ├── 📁 Feature
│   │   ├── 📁 Auth
│   │   └── 📁 Exam
│   │       ├── 🐘 ArchitectureGuardsTest.php
│   │       ├── 🐘 AttemptAuthorizationTest.php
│   │       ├── 🐘 ExpirationTest.php
│   │       ├── 🐘 LifecycleTest.php
│   │       ├── 🐘 OperatorControlTest.php
│   │       └── 🐘 ResultVisibilityTest.php
│   ├── 📁 Unit
│   └── 🐘 TestCase.php
├── ⚙️ .editorconfig
├── ⚙️ .env.example
├── ⚙️ .gitattributes
├── ⚙️ .gitignore
├── ⚙️ .mcp.json
├── 📝 CLAUDE.md
├── 📝 README.md
├── 📄 artisan
├── ⚙️ boost.json
├── ⚙️ composer.json
├── ⚙️ package-lock.json
├── ⚙️ package.json
├── ⚙️ phpunit.xml
├── 📄 postcss.config.js
├── 📄 tailwind.config.js
└── 📄 vite.config.js
```