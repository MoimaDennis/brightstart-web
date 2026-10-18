<?php
session_start();
$loggedIn = isset($_SESSION['admin_id']);
$currentUser = $loggedIn ? htmlspecialchars($_SESSION['admin_name']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bright Start – Education Initiative</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --green-dark: #1a3a2a;
    --green-mid: #2d5a3d;
    --green-deep: #0f2419;
    --lime: #b8e04a;
    --lime-soft: #d4f06e;
    --white: #f9f8f4;
    --off-white: #ede9df;
    --text-muted: #a8b8a0;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body { font-family: "DM Sans", sans-serif; background: var(--green-deep); color: var(--white); overflow-x: hidden; }

/* NAV */
nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 200;
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.1rem 4rem;
    background: rgba(10,24,16,0.9); backdrop-filter: blur(14px);
    border-bottom: 1px solid rgba(184,224,74,0.15);
}
.nav-logo { display: flex; align-items: center; gap: 0.7rem; text-decoration: none; cursor: pointer; }
.nav-logo span { font-family: "Playfair Display", serif; font-size: 1.4rem; font-weight: 700; color: var(--white); }
.nav-logo span em { color: var(--lime); font-style: normal; }
.nav-links { display: flex; gap: 2.2rem; list-style: none; }
.nav-links a {
    color: var(--off-white); text-decoration: none; font-size: 0.85rem;
    font-weight: 500; letter-spacing: 0.04em; text-transform: uppercase;
    opacity: 0.8; transition: opacity 0.2s, color 0.2s;
}
.nav-links a:hover { opacity: 1; color: var(--lime); }
.nav-auth { display: flex; align-items: center; gap: 0.75rem; }
.nav-cta, .btn-signin, .btn-dashboard {
    padding: 0.55rem 1.4rem; border-radius: 100px; font-weight: 600;
    font-size: 0.85rem; text-decoration: none; transition: background 0.2s, transform 0.2s;
    border: 1.5px solid transparent; cursor: pointer; font-family: inherit;
}
.nav-cta { background: var(--lime); color: var(--green-deep); }
.nav-cta:hover { background: var(--lime-soft); transform: translateY(-1px); }
.btn-signin {
    background: transparent; color: var(--white); border-color: rgba(255,255,255,0.3);
}
.btn-signin:hover { border-color: var(--lime); color: var(--lime); }
.btn-dashboard { background: rgba(184,224,74,0.12); color: var(--lime); border-color: rgba(184,224,74,0.4); }
.btn-dashboard:hover { background: rgba(184,224,74,0.22); }
.hamburger { display: none; flex-direction: column; gap: 5px; background: none; border: none; cursor: pointer; padding: 6px; }
.hamburger span { width: 24px; height: 2px; background: var(--white); border-radius: 2px; }

/* HERO */
#hero { position: relative; width: 100%; height: 100vh; min-height: 600px; overflow: hidden; }
.slide { position: absolute; inset: 0; opacity: 0; transition: opacity 1s ease; }
.slide.active { opacity: 1; z-index: 2; }
.slide img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
.slide::after {
    content: ""; position: absolute; inset: 0; z-index: 3;
    background: linear-gradient(135deg, rgba(10,24,16,0.78) 0%, rgba(10,24,16,0.32) 60%, rgba(10,24,16,0.55) 100%);
}
.hero-text { position: absolute; top: 50%; left: 4rem; transform: translateY(-50%); z-index: 5; max-width: 600px; pointer-events: none; }
.hero-text .badge {
    display: inline-flex; align-items: center; gap: 0.5rem;
    border: 1px solid rgba(184,224,74,0.5); background: rgba(184,224,74,0.08);
    color: var(--lime); font-size: 0.72rem; font-weight: 600;
    letter-spacing: 0.1em; text-transform: uppercase;
    padding: 0.4rem 1rem; border-radius: 100px; margin-bottom: 1.4rem;
}
.hero-text h1 {
    font-family: "Playfair Display", serif;
    font-size: clamp(2.4rem, 5vw, 4.5rem);
    font-weight: 900; line-height: 1.07; color: var(--white); margin-bottom: 1.1rem;
}
.hero-text h1 em { font-style: normal; color: var(--lime); }
.hero-text p {
    font-size: 1.05rem; color: var(--off-white); opacity: 0.88;
    line-height: 1.7; max-width: 430px; margin-bottom: 2rem;
}
.hero-btns { display: flex; gap: 1rem; pointer-events: all; flex-wrap: wrap; }
.btn-primary {
    background: var(--lime); color: var(--green-deep);
    padding: 0.85rem 2rem; border-radius: 100px; font-weight: 700;
    font-size: 0.9rem; text-decoration: none; transition: background 0.2s, transform 0.2s;
    display: inline-block; border: none; cursor: pointer; font-family: inherit;
}
.btn-primary:hover { background: var(--lime-soft); transform: translateY(-2px); }
.btn-outline {
    border: 1.5px solid rgba(255,255,255,0.4); color: var(--white);
    padding: 0.85rem 2rem; border-radius: 100px; font-weight: 500;
    font-size: 0.9rem; text-decoration: none; transition: border-color 0.2s, background 0.2s;
    display: inline-block;
}
.btn-outline:hover { border-color: var(--lime); color: var(--lime); background: rgba(184,224,74,0.08); }

/* Slide captions */
.slide-caption {
    position: absolute; bottom: 130px; left: 4rem; z-index: 4;
    max-width: 460px; opacity: 0; transform: translateY(16px);
    transition: opacity 0.8s 0.4s, transform 0.8s 0.4s;
}
.slide.active .slide-caption { opacity: 1; transform: translateY(0); }
.slide-caption .tag {
    display: inline-block; background: var(--lime); color: var(--green-deep);
    font-size: 0.68rem; font-weight: 700; letter-spacing: 0.1em;
    text-transform: uppercase; padding: 0.28rem 0.8rem;
    border-radius: 100px; margin-bottom: 0.6rem;
}
.slide-caption h2 {
    font-family: "Playfair Display", serif;
    font-size: clamp(1.1rem, 2vw, 1.6rem);
    font-weight: 700; line-height: 1.2; color: var(--white); margin-bottom: 0.4rem;
}
.slide-caption p { font-size: 0.88rem; color: var(--off-white); opacity: 0.8; line-height: 1.5; }

/* Controls */
.carousel-controls { position: absolute; bottom: 2.5rem; left: 4rem; z-index: 10; display: flex; align-items: center; gap: 0.75rem; }
.carousel-dot {
    width: 8px; height: 8px; border-radius: 100px;
    background: rgba(255,255,255,0.3); cursor: pointer;
    transition: background 0.3s, width 0.3s; border: none;
}
.carousel-dot.active { background: var(--lime); width: 28px; }
.carousel-arrows { position: absolute; bottom: 2rem; right: 4rem; z-index: 10; display: flex; gap: 0.75rem; }
.arrow-btn {
    width: 46px; height: 46px; border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,0.3); background: rgba(10,24,16,0.5);
    backdrop-filter: blur(8px); color: white; cursor: pointer; font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    transition: border-color 0.2s, background 0.2s;
}
.arrow-btn:hover { border-color: var(--lime); background: rgba(184,224,74,0.15); }
.slide-counter {
    position: absolute; top: 50%; right: 4rem; transform: translateY(-50%);
    z-index: 10; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
}
.counter-current { font-family: "Playfair Display", serif; font-size: 2.5rem; font-weight: 900; color: var(--lime); line-height: 1; }
.counter-sep { width: 1px; height: 28px; background: rgba(255,255,255,0.3); }
.counter-total { font-size: 1rem; color: rgba(255,255,255,0.4); }
.progress-bar { position: absolute; bottom: 0; left: 0; height: 3px; background: var(--lime); z-index: 10; width: 0%; }

/* STATS */
.stats-bar { background: var(--lime); padding: 1.5rem 4rem; display: flex; gap: 0; justify-content: space-around; align-items: center; }
.stat-item { text-align: center; flex: 1; }
.stat-item + .stat-item { border-left: 1px solid rgba(10,24,16,0.2); }
.stat-num { font-family: "Playfair Display", serif; font-size: 2rem; font-weight: 900; color: var(--green-deep); display: block; }
.stat-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(10,24,16,0.7); }

/* ABOUT */
#about { padding: 6rem 4rem; display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: center; background: var(--green-dark); }
.section-tag {
    display: inline-block; font-size: 0.7rem; font-weight: 700;
    letter-spacing: 0.12em; text-transform: uppercase; color: var(--lime);
    border: 1px solid rgba(184,224,74,0.3); padding: 0.3rem 0.85rem;
    border-radius: 100px; margin-bottom: 1.25rem;
}
.about-text h2 { font-family: "Playfair Display", serif; font-size: clamp(1.8rem, 3vw, 2.8rem); font-weight: 700; line-height: 1.2; margin-bottom: 1.25rem; }
.about-text h2 em { color: var(--lime); font-style: normal; }
.about-text p { color: var(--text-muted); line-height: 1.8; margin-bottom: 1.1rem; font-size: 1rem; }
.about-img { position: relative; border-radius: 1rem; overflow: hidden; height: 420px; }
.about-img img { width: 100%; height: 100%; object-fit: cover; }
.about-img::before {
    content: ""; position: absolute; inset: 0;
    border: 2px solid rgba(184,224,74,0.3); border-radius: 1rem; z-index: 2; pointer-events: none;
}

/* PROGRAMS */
#programs { padding: 6rem 4rem; background: var(--green-deep); }
.section-header { text-align: center; margin-bottom: 3rem; }
.section-header h2 { font-family: "Playfair Display", serif; font-size: clamp(1.8rem, 3vw, 2.6rem); font-weight: 700; }
.section-header h2 em { color: var(--lime); font-style: normal; }
.programs-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
.program-card {
    background: var(--green-dark); border: 1px solid rgba(184,224,74,0.12);
    border-radius: 1rem; padding: 2rem; transition: border-color 0.3s, transform 0.3s;
    cursor: pointer;
}
.program-card:hover { border-color: rgba(184,224,74,0.4); transform: translateY(-4px); }
.program-icon {
    width: 52px; height: 52px; background: rgba(184,224,74,0.12); border-radius: 0.75rem;
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.1rem;
}
.program-card h3 { font-family: "Playfair Display", serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 0.6rem; }
.program-card > p { color: var(--text-muted); font-size: 0.88rem; line-height: 1.65; margin-bottom: 1rem; }
.program-projects { display: none; margin-top: 0.75rem; }
.program-card.open .program-projects { display: block; }
.program-toggle {
    display: inline-flex; align-items: center; gap: 0.4rem;
    color: var(--lime); font-size: 0.8rem; font-weight: 600; background: none;
    border: 1px solid rgba(184,224,74,0.35); border-radius: 100px;
    padding: 0.3rem 0.85rem; cursor: pointer; transition: background 0.2s; margin-top: 0.5rem; font-family: inherit;
}
.program-toggle:hover { background: rgba(184,224,74,0.1); }
.project-item { background: rgba(184,224,74,0.06); border-left: 3px solid var(--lime); border-radius: 0 0.5rem 0.5rem 0; padding: 0.75rem 1rem; margin-bottom: 0.6rem; }
.project-item h4 { font-size: 0.9rem; font-weight: 600; color: var(--white); margin-bottom: 0.25rem; }
.project-item p { font-size: 0.8rem; color: var(--text-muted); line-height: 1.5; }
.project-item .proj-tag {
    display: inline-block; font-size: 0.65rem; background: var(--lime);
    color: var(--green-deep); padding: 0.15rem 0.5rem; border-radius: 100px;
    font-weight: 700; text-transform: uppercase; margin-bottom: 0.35rem;
}

/* CTA */
#cta { padding: 6rem 4rem; background: var(--lime); color: var(--green-deep); }
.cta-inner { max-width: 1200px; margin: 0 auto; text-align: center; }
.cta-inner h2 { font-family: "Playfair Display", serif; font-size: clamp(1.8rem, 3vw, 2.6rem); font-weight: 700; margin-bottom: 1rem; }
.cta-inner p { max-width: 620px; margin: 0 auto 2rem; line-height: 1.7; opacity: 0.85; }
.cta-inner .btn-primary { background: var(--green-deep); color: var(--lime); }
.cta-inner .btn-primary:hover { background: var(--green-dark); }

/* FOOTER */
footer { padding: 3rem 4rem; background: var(--green-dark); text-align: center; color: var(--text-muted); font-size: 0.85rem; }
footer span { color: var(--lime); font-weight: 700; }

/* RESPONSIVE */
@media (max-width: 900px) {
    nav { padding: 1rem 1.5rem; }
    .nav-links { display: none; }
    .hamburger { display: flex; }
    .nav-auth .nav-cta { display: none; }
    .hero-text { left: 1.5rem; right: 1.5rem; max-width: 100%; }
    .slide-counter { display: none; }
    .slide-caption { left: 1.5rem; bottom: 100px; }
    #about { grid-template-columns: 1fr; padding: 4rem 1.5rem; gap: 2rem; }
    #programs, #cta, .stats-bar, footer { padding: 4rem 1.5rem; }
    .programs-grid { grid-template-columns: 1fr; }
    .stats-bar { flex-wrap: wrap; gap: 1.5rem; }
    .stat-item { flex: 1 1 40%; }
    .carousel-arrows { right: 1.5rem; }
    .carousel-controls { left: 1.5rem; }
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
    <a href="#hero" class="nav-logo" style="text-decoration:none;">
        <span>Bright <em>Start</em></span>
    </a>
    <ul class="nav-links" id="navLinks">
        <li><a href="#about">About</a></li>
        <li><a href="#programs">Programs</a></li>
        <li><a href="#cta">Get Involved</a></li>
    </ul>
    <div class="nav-auth">
        <?php if ($loggedIn): ?>
            <a href="dashboard.php" class="btn-dashboard">🏠 Dashboard</a>
            <a href="logout.php" class="btn-signin">Sign Out</a>
        <?php else: ?>
            <a href="login.php" class="btn-signin">Sign In</a>
            <a href="register.php" class="nav-cta">Join Us</a>
        <?php endif; ?>
    </div>
</nav>

<!-- HERO -->
<section id="hero">
    <div class="slide active" data-index="0">
        <img src="images/slide1.jpg" alt="Bright Start learners">
        <div class="slide-caption">
            <span class="tag">Education Support System</span>
            <h2>Empowering Every Learner</h2>
            <p>Centralised management for students, volunteers, and programmes across South Africa's communities.</p>
        </div>
    </div>
    <div class="slide" data-index="1">
        <img src="images/slide2.jpg" alt="Volunteer mentorship session">
        <div class="slide-caption">
            <span class="tag">Volunteer Management</span>
            <h2>Dedicated Volunteers, Real Impact</h2>
            <p>Schedule tutoring sessions, assign volunteers, and track attendance — all in one place.</p>
        </div>
    </div>
    <div class="slide" data-index="2">
        <img src="images/slide3.jpg" alt="Community education programme">
        <div class="slide-caption">
            <span class="tag">Community Impact</span>
            <h2>Empowering Communities Through Education</h2>
            <p>Walking alongside learners from disadvantaged communities toward brighter futures across South Africa.</p>
        </div>
    </div>

    <!-- Static hero text -->
    <div class="hero-text">
        <div class="badge"><span>●</span> Education Initiative</div>
        <h1>Education<br><em>Changes</em><br>Everything</h1>
        <p>A non-profit organisation focused on improving access to education for learners in disadvantaged communities.</p>
        <div class="hero-btns">
            <a href="#programs" class="btn-primary">Our Programs</a>
            <a href="#cta" class="btn-outline">Support Us</a>
        </div>
    </div>

    <div class="slide-counter">
        <span class="counter-current" id="counterCurrent">01</span>
        <div class="counter-sep"></div>
        <span class="counter-total">03</span>
    </div>

    <div class="carousel-controls" id="dotsContainer">
        <button class="carousel-dot active" data-go="0"></button>
        <button class="carousel-dot" data-go="1"></button>
        <button class="carousel-dot" data-go="2"></button>
    </div>

    <div class="carousel-arrows">
        <button class="arrow-btn" id="prevBtn">&#8592;</button>
        <button class="arrow-btn" id="nextBtn">&#8594;</button>
    </div>

    <div class="progress-bar" id="progressBar"></div>
</section>

<!-- STATS -->
<div class="stats-bar">
    <div class="stat-item"><span class="stat-num">2,400+</span><span class="stat-label">Learners Supported</span></div>
    <div class="stat-item"><span class="stat-num">18</span><span class="stat-label">Partner Schools</span></div>
    <div class="stat-item"><span class="stat-num">92%</span><span class="stat-label">Matric Pass Rate</span></div>
    <div class="stat-item"><span class="stat-num">6</span><span class="stat-label">Years of Impact</span></div>
</div>

<!-- ABOUT -->
<section id="about">
    <div class="about-text">
        <span class="section-tag">Who We Are</span>
        <h2>Bridging the Gap in South African <em>Education</em></h2>
        <p>Bright Start is a non-profit organisation committed to transforming educational outcomes for learners in under-resourced communities across South Africa.</p>
        <p>We believe every child, regardless of circumstances, deserves access to quality education, skilled teachers, and the tools needed to succeed in an ever-changing world.</p>
        <p>Through tutoring, mentorship, technology access programmes, and community engagement, we work hand-in-hand with schools, families, and partners to make this vision a reality.</p>
        <a href="#programs" class="btn-primary" style="margin-top:1rem;">Explore Our Work</a>
    </div>
    <div class="about-img">
        <img src="images/WhatsApp Image 2026-06-04 at 19.24.36.jpg" alt="Bright Start community programme">
    </div>
</section>

<!-- PROGRAMS -->
<section id="programs">
    <div class="section-header">
        <div class="section-tag">What We Do</div>
        <h2>Programs That Make a <em>Difference</em></h2>
        <p style="color:var(--text-muted);margin-top:0.75rem;font-size:0.95rem;">Click any program card to explore the projects inside.</p>
    </div>
    <div class="programs-grid">

        <div class="program-card" onclick="toggleCard(this)">
            <div class="program-icon">📚</div>
            <h3>Academic Tutoring</h3>
            <p>Structured after-school tutoring in core subjects — Maths, Science, English and more — delivered by trained educators and university volunteers.</p>
            <button class="program-toggle">View Projects ▾</button>
            <div class="program-projects">
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>Project Thuto — Maths for All</h4>
                    <p>Weekly Maths clinics at 12 partner schools, using hands-on problem-solving and peer teaching to close the Grade 8–10 numeracy gap.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>Science Saturday Labs</h4>
                    <p>Monthly practical science workshops that bring curriculum concepts to life using low-cost experiments — reaching 400+ learners per term.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">Upcoming</span>
                    <h4>English Literacy Bridge</h4>
                    <p>A reading and writing acceleration programme targeting Grade 4–6 learners whose home language is not English.</p>
                </div>
            </div>
        </div>

        <div class="program-card" onclick="toggleCard(this)">
            <div class="program-icon">💻</div>
            <h3>Digital Literacy</h3>
            <p>Equipping learners with computer skills, internet access, and digital tools essential for the modern economy and tertiary education environments.</p>
            <button class="program-toggle">View Projects ▾</button>
            <div class="program-projects">
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>Code for Change</h4>
                    <p>An introductory coding bootcamp teaching HTML, Python basics, and problem-solving to Grade 9–11 learners over 10 weeks.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>ConnectED Device Drive</h4>
                    <p>Refurbishing and distributing donated laptops and tablets to learners without home devices, paired with free data bundles.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">Pilot</span>
                    <h4>AI Literacy for Learners</h4>
                    <p>A new pilot programme introducing AI concepts and responsible use to Grade 10 learners, preparing them for an AI-driven future.</p>
                </div>
            </div>
        </div>

        <div class="program-card" onclick="toggleCard(this)">
            <div class="program-icon">🎓</div>
            <h3>Matric Support</h3>
            <p>Intensive preparation and support for Grade 12 learners — ensuring they are confident and ready to achieve distinction in their final year exams.</p>
            <button class="program-toggle">View Projects ▾</button>
            <div class="program-projects">
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>Final Push Camp</h4>
                    <p>An intensive 5-day pre-exam study camp each September covering all major subjects, past papers, and exam technique workshops.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>Past Paper Portal</h4>
                    <p>A curated online library of 10 years of NSC past papers with model answers, accessible free to all registered learners.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">New</span>
                    <h4>Stress-Less Programme</h4>
                    <p>Mindfulness, time management, and mental health support sessions specifically designed for matric learners managing exam pressure.</p>
                </div>
            </div>
        </div>

        <div class="program-card" onclick="toggleCard(this)">
            <div class="program-icon">🤝</div>
            <h3>Mentorship</h3>
            <p>One-on-one mentorship connecting learners with professionals and recent graduates who guide and inspire them on their academic journeys.</p>
            <button class="program-toggle">View Projects ▾</button>
            <div class="program-projects">
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>Rise Mentorship Network</h4>
                    <p>140+ volunteer mentors from engineering, medicine, law, and education paired with Grade 10–12 learners for bi-weekly check-ins.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>Women in STEM Circle</h4>
                    <p>A mentorship and inspiration programme connecting young women with female STEM professionals, including site visits and talks.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">Upcoming</span>
                    <h4>Alumni Ambassador Programme</h4>
                    <p>Former Bright Start learners who have entered university or employment return as mentors, closing the full circle of support.</p>
                </div>
            </div>
        </div>

        <div class="program-card" onclick="toggleCard(this)">
            <div class="program-icon">🏫</div>
            <h3>School Partnerships</h3>
            <p>Working directly with under-resourced schools to provide resources, teacher support, and infrastructure improvements that benefit entire communities.</p>
            <button class="program-toggle">View Projects ▾</button>
            <div class="program-projects">
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>Library Rebuild Initiative</h4>
                    <p>Stocking and modernising school libraries with curriculum-aligned books, e-readers, and quiet study spaces across 6 schools.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>Teacher CPD Workshops</h4>
                    <p>Monthly continuous professional development workshops helping teachers integrate technology and active-learning strategies into their classrooms.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">Completed</span>
                    <h4>Science Lab Upgrade — Phase 1</h4>
                    <p>Fully equipped two under-resourced school labs with modern apparatus, safety equipment, and digital microscopes.</p>
                </div>
            </div>
        </div>

        <div class="program-card" onclick="toggleCard(this)">
            <div class="program-icon">🌱</div>
            <h3>Career Pathways</h3>
            <p>Helping learners explore career options, apply for bursaries and university programmes, and transition into higher education and employment.</p>
            <button class="program-toggle">View Projects ▾</button>
            <div class="program-projects">
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>Bursary Navigator</h4>
                    <p>A dedicated team helping matric learners identify, apply for, and secure bursaries — 87 bursaries secured in 2025 alone.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">Active</span>
                    <h4>Career Expo &amp; Job Shadow Days</h4>
                    <p>Annual expo connecting learners with 50+ employers and universities, plus job shadow placements in high-demand sectors.</p>
                </div>
                <div class="project-item">
                    <span class="proj-tag">New</span>
                    <h4>Entrepreneurship Seedbed</h4>
                    <p>A 12-week programme teaching business fundamentals and pitching skills, culminating in a youth entrepreneur showcase event.</p>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- CTA -->
<section id="cta">
    <div class="cta-inner">
        <h2>Help Us Reach More Learners</h2>
        <p>Your support enables us to expand our programmes and give more South African children the education they deserve. Every contribution — big or small — changes lives.</p>
        <?php if ($loggedIn): ?>
            <a href="dashboard.php" class="btn-primary">Go to Dashboard</a>
        <?php else: ?>
            <a href="register.php" class="btn-primary">Join Bright Start</a>
            <a href="login.php" class="btn-outline" style="margin-left:1rem;border-color:rgba(15,36,25,0.3);color:var(--green-deep);">Sign In</a>
        <?php endif; ?>
    </div>
</section>

<footer>
    <p>&copy; 2026 <span>Bright Start</span> Education Initiative</p>
</footer>

<script>
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.carousel-dot');
const counter = document.getElementById('counterCurrent');
const progress = document.getElementById('progressBar');
let current = 0;
let autoTimer = null;
const INTERVAL = 6000;

function pad(n) { return String(n).padStart(2, '0'); }

function goToSlide(n) {
    slides[current].classList.remove('active');
    dots[current].classList.remove('active');
    current = (n + slides.length) % slides.length;
    slides[current].classList.add('active');
    dots[current].classList.add('active');
    counter.textContent = pad(current + 1);
    resetProgress();
}

function changeSlide(dir) { goToSlide(current + dir); resetAuto(); }

function resetAuto() {
    clearInterval(autoTimer);
    autoTimer = setInterval(() => goToSlide(current + 1), INTERVAL);
}

function resetProgress() {
    progress.style.transition = 'none';
    progress.style.width = '0%';
    progress.getBoundingClientRect();
    progress.style.transition = 'width ' + INTERVAL + 'ms linear';
    progress.style.width = '100%';
}

document.getElementById('prevBtn').addEventListener('click', () => changeSlide(-1));
document.getElementById('nextBtn').addEventListener('click', () => changeSlide(1));
dots.forEach((dot, i) => dot.addEventListener('click', () => { goToSlide(i); resetAuto(); }));

document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft') changeSlide(-1);
    if (e.key === 'ArrowRight') changeSlide(1);
});

let touchStartX = 0;
const hero = document.getElementById('hero');
hero.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
hero.addEventListener('touchend', e => {
    const diff = touchStartX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) { changeSlide(diff > 0 ? 1 : -1); resetAuto(); }
}, { passive: true });

resetAuto();
resetProgress();

function toggleCard(card) {
    card.classList.toggle('open');
    const btn = card.querySelector('.program-toggle');
    btn.textContent = card.classList.contains('open') ? 'Hide Projects ▴' : 'View Projects ▾';
}
</script>

</body>
</html>