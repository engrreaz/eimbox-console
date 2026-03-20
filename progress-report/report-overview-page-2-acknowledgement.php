<?php

?>
<style>
    .text-small {
        font-size: 14px;
        font-style: italic;
        text-align: justify;
    }
</style>
<div class="page-a4">
    <?php include 'assets/pad/letter-head.php'; ?>

    <div>
        <h3 class="text-center">Acknowledgements by headteacher</h3>
        <div class="text-small">
            We, at <b><?php echo $scname; ?></b>, take this opportunity to express our heartfelt gratitude and
            appreciation to
            all
            those who contributed to the successful conduct and completion of the
            <b><?php echo $exam . ' Examination, ' . $sy; ?></b>.
        </div>

        <h5 class=" mt-3">Acknowledgment for Teachers</h5>
        <div class="text-small">
            Our deepest appreciation goes to our dedicated teachers and staff, whose relentless efforts ensured that the
            examination process was conducted smoothly and efficiently.

            Your meticulous preparation, guidance, and teaching have empowered our students to approach their exams with
            confidence and competence.
            The time and energy you devoted to setting question papers, evaluating answer scripts, and supporting
            administrative activities have been invaluable.
            Your unwavering commitment to excellence continues to be the cornerstone of our institution’s success.
        </div>

        <h5 class=" mt-3">Acknowledgment for Students</h5>
        <div class="text-small">
            A special acknowledgment to our students, who demonstrated perseverance, discipline, and enthusiasm
            throughout
            the examination period.

            Your hard work and dedication to academic excellence are truly commendable.
            By embracing challenges and striving for your personal best, you have shown that learning and growth are
            continuous journeys.
            We are incredibly proud of your achievements and the resilience you’ve displayed.
        </div>

        <h5 class=" mt-3">Acknowledgment for Guardians/Parents</h5>
        <div class="text-small">
            We extend our sincere thanks to the parents and guardians for their unwavering support and encouragement
            during
            this important phase of your children’s education.

            Your active involvement, whether by ensuring a conducive learning environment at home, providing emotional
            support, or communicating with the institution, has played a crucial role in their success.
            Your collaboration with the institution is a testament to the strong partnership we share in nurturing the
            future of our students.
        </div>

        <h5 class=" mt-3"> Final Note of Gratitude</h5>
        <div class="text-small">
            The collective efforts of our teachers, students, and guardians have made this examination session a
            meaningful
            and successful endeavor. Together, we have upheld the values of teamwork, commitment, and excellence, which
            are
            at the heart of our institution’s mission.

            As we celebrate the completion of this examination, we also look forward to continuing this journey of
            learning
            and growth together. Let us remain united in our efforts to empower our students and build a brighter
            future.

            Thank you for your unwavering support and dedication.
        </div>


        <pre>
    With gratitude,
    [<?php echo $headname; ?>]
    <?php echo $headtitle; ?>
    <?php echo $scname; ?>
    <?php echo $scaddress; ?>
    </pre>




    </div>


</div>