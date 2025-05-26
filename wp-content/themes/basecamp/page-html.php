<?php
/*
 * Template Name: HTML Elements Page
 * Author: Joshua Michaels for studio.bio
 * 
 * This page is for developers to test all of your styles.
 * Create a new WordPress page and use this page template. 
 * 
 * Everything is hardcoded in here to avoid crazy WP formatting
 * stuffs.
 * 
 *
*/
?>

<?php get_header(); ?>

<main id="main" class="" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

	<section id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?>>

        <h1 class="page-title" itemprop="headline"><?php the_title(); ?></h1>

        <?php // Delete or comment out if you don't need this on your page or post. Edit in /templates/byline.php ?>
        <?php get_template_part( 'templates/byline'); ?>

            <section>
                <p class="intro">This is an intro paragraph. Here is a test page with a myriad of HTML elements. We are using this to test all of our theme's styles. As far as I can tell it uses almost every HTML element known to humankind. If you find a better HTML elements sample page, let us know.</p>
                <h1 class="other-class">First Header h1</h1>
                <p class="test-class">
                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis
                    praesentium voluptatum deleniti atque corrupti quos.
                </p>
                <h2>Second header h2</h2>
                <p class="test-class">
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat
                    non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                </p>
                <h3>Third header h3</h3>
                <p class="test-class">
                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis
                    praesentium voluptatum deleniti atque corrupti quos dolores et quas
                    molestias excepturi sint occaecati cupiditate non provident, similique sunt
                    in culpa qui officia deserunt mollitia animi, id est laborum et dolorum
                    fuga. Et harum quidem rerum facilis est et expedita distinctio.
                </p>
                <h4>Fourth header h4</h4>
                <p class="test-class">
                    Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet,
                    consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt
                    ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima
                    veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi
                    ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit
                    qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum
                    qui dolorem eum fugiat quo voluptas nulla pariatur?"
                </p>
                <h5>Fifth header h5</h5>
                <p class="test-class">
                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium
                    doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore
                    veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim
                    ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia
                    consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.
                </p>
                <h6>Sixth header h6</h6>
                <p class="test-class">
                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis
                    praesentium voluptatum deleniti atque corrupti quos.
                </p>
            </section>

            <hr />

            <section>
                <h2 class="other-class">Links</h2>
                <h3 class="other-class"><a href="#">Link Heading</a></h3>
                <p><a href="#">Sample text link</a></p>
                <p><a class="blue-btn" href="#">Button class link</a></p>
            </section>

            <hr />

            <section>
                <h2 class="other-class">Lists</h2>
                <h3>Unordered list</h3>
                <ul>
                    <li>Orange</li>
                    <li>Apple</li>
                    <li>Rhubarb</li>
                    <li>Rasberry</li>
                    <li>Blueberry</li>
                    <li>Cherry</li>
                </ul>
                <h3>Ordered list</h3>
                <ol>
                    <li>First</li>
                    <li>Second</li>
                    <li>Third</li>
                    <li>Fourth</li>
                    <li>Fifth</li>
                    <li>Sixth</li>
                </ol>
                <h3>Definition list</h3>
                <dl>
                    <dt>Kick</dt>
                    <dd>808</dd>
                    <dt>Snare</dt>
                    <dd>909</dd>
                </dl>
                <dl>
                    <dt> Maine </dt>
                    <dd> Augusta </dd>
                    <dt> California </dt>
                    <dd> Sacremento </dd>
                    <dt> Oregon </dt>
                    <dd> Salem </dd>
                    <dt> New York </dt>
                    <dd> Albany </dd>
                </dl>
                <dl>
                    <dt> Ascender </dt>
                    <dd> The part of certain lowercase letters that extends above the x-height of a font.  </dd>
                    <dt> Font </dt>
                    <dd> Traditionally, a complete set of characters for one typeface at
                    one particular type size. Often used more loosely as a synonym for
                    "typeface".
                    </dd>
                    <dt> Golden Section </dt>
                    <dd>
                    The ideal proportion according to the ancient Greeks. It is visualized as the
                    division of a line into two unequal segments in such a way that the ratio of the
                    smaller segment to the larger segment is equal to the ratio of the larger to the
                    whole. It is usually defined as 21:34, that is, 21/34 and 34/(21+34) both equal
                    approximately 0.618. A rectangle whose sides are of this proportion is called a
                    "golden rectangle". Golden rectangles can be found in the proportions of the
                    Parthenon and many medieval manuscripts.
                    </dd>
                </dl>
                <h3>Details and Summary</h3>
                <details name="faq" >
                    <summary>FAQ 1</summary>
                    <p>Can you smell that?</p>
                </details>

                <details name="faq" >
                    <summary>FAQ 2</summary>
                    <p>Something really stinks.</p>
                </details>

                <details name="faq" >
                    <summary>FAQ 3</summary>
                    <p>Oh, it's you. 🙂</p>
                </details>
            </section>

            <hr />

            <section>
                <h2 class="other-class">Dialog with a form</h2>
                <dialog>
                    <p>This dialog has entry and exit animations.</p>
                    <form method="dialog">
                    <button>OK</button>
                    </form>
                </dialog>
                <p><button onclick="document.querySelector('dialog').showModal()">Open Dialog</button></p>
            </section>

            <hr />

            <section>
                <h2 class="other-class">Forms</h2>
                <form>
                    <fieldset>
                        <!--
                        Every fieldset must contain a legend. IE barfs if it's not there.
                        It's no fun.
                        -->
                        <legend>Legend Example</legend>
                        <div class="form--row">
                            <label for="">Search</label>
                            <input class="with-description" type="text" placeholder="Search" id="" />
                            <p class="test-class">Helper text if necessary.</p>
                        </div>
                        <div class="form--row">
                            <label for="">Text Input Label</label>
                            <input class="with-description" type="text" placeholder="Type Something..." id="" />
                            <p class="test-class">Helper text if necessary.</p>
                        </div>
                        <div class="form--row">
                            <label>Password <span class="required">*</span></label>
                            <input class="with-description" type="password" required />
                            <p class="form--error">Error messages when appropriate.</p>
                        </div>
                        <div class="form--row">
                            <label for="first-name">First Name</label>
                            <input type="text" id="first-name" />
                        </div>
                        <div class="form--row">
                            <label for="last-name">Last Name</label>
                            <input type="text" id="last-name" />
                        </div>
                        <div class="form--row">
                            <label for="email">Email</label>
                            <input type="email" id="email" />
                        </div>
                        <div class="form--row">
                            <label for="gender">Dropdown</label>
                            <select>
                                <option>Option 1</option>
                                <option>Option 2</option>
                                <option>Option 3</option>
                            </select>
                        </div>
                        <div class="form--row">
                            <label>Radio Buttons</label>
                            <ul class="nostyle radio-buttons">
                                <li><label><input type="radio" /> Label 1</label></li>
                                <li><label><input type="radio" /> Label 2</label></li>
                                <li><label><input type="radio" /> Label 3</label></li>
                            </ul>
                        </div>
                        <div class="form--row">
                            <label for="url">URL Input</label>
                            <input type="url" placeholder="http://mrmrs.cc" />
                        </div>
                        <div class="form--row">
                            <label>Text area</label>
                            <textarea></textarea>
                        </div>
                        <div class="form--row">
                            <label><input type="checkbox" /> This is a checkbox.</label>
                        </div>
                        <div class="form--row">
                            <input class="blue-btn" type="submit" value="Submit" />
                        </div>
                    </fieldset>
                </form>
            </section>

            <hr />

            <section>
                <h2 class="other-class">Buttons</h2>
                <button>Regular Button</button>
                <button class="purple-btn">Purple Button</button>
                <button>Large Blue Button</button>
            </section>

            <hr />

            <section>
                <h2 class="other-class">An Example Article</h2>
                <article>
                    <h1 class="other-class">Title</h1>
                    <p class="test-class">
                    Lorem ipsum dolor sit amet, <b>consectetur adipisicing elit</b>, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                    quis nostrud <em>exercitation ullamco laboris nisi ut aliquip ex ea commodo
                    consequat</em>. Duis aute irure dolor in reprehenderit in voluptate velit esse
                    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat
                    non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                    </p>
                    <blockquote>
                        <p class="test-class">
                        This is a GREAT pull quote.
                        </p>
                        <a href="#">- Author</a>
                    </blockquote>
                    <p class="test-class">
                    Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet,
                    consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt
                    ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima
                    veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi
                    ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit
                    qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum
                    qui dolorem eum fugiat quo voluptas nulla pariatur?"
                    </p>
                    <p class="test-class">
                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis
                    praesentium voluptatum deleniti atque corrupti quos dolores et quas
                    molestias excepturi sint occaecati cupiditate non provident, similique sunt
                    in culpa qui officia deserunt mollitia animi, id est laborum et dolorum
                    fuga. Et harum quidem rerum facilis est et expedita distinctio.
                    </p>
                </article>
            </section>

            <hr />

            <section>
                <h2 class="other-class">Code examples</h2>
                <pre>
                    <code>  
                    sudo ipfw pipe 1 config bw 256KByte/s
                    sudo ipfw add 1 pipe 1 src-port 3000
                    </code>
                </pre>
            </section>

            <hr />

            <footer>
                <h2 class="other-class">Footer</h2>
                <p class="test-class">Copyright 2013. Made with love by <a href="http://mrmrs.cc" title="MRMRS - Designer">mrmrs</a>.</p>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Jobs</a></li>
                </ul>
                <ul>
                    <li><a href="http://twitter.com" title=" on Twitter">Twitter</a></li>
                    <li><a href="http://pinterest.com" title=" on Pinterest">Pinterest</a></li>
                    <li><a href="http://instagram.com" title=" on Instagram">Instagram</a></li>
                    <li><a href="http://dribbbble.com" title=" on Dribbble">Dribbble</a></li>
                    <li><a href="http://github.com" title=" on Github">Github</a></li>
                </ul>
            </footer>

            <hr />

            <section>
                <h2 class="other-class">New hawtness</h2>
                Progress bar: <progress value="80" max="100">80 %</progress>
                <p class="test-class">We are this close to the goal: <meter min="0" max="1000" value="824">$824</meter>.</p>
            </section>

            <hr />

            <section>
                <h2 class="other-class">Random Stuff</h2>
                <p><small>This is for things like copyright info</small>
                    <s>Content that isn't accurate or relevant anymore.</s>
                    <span>Generic span wrapper</span>
                    <abbr>HTML How to meet ladies</abbr>
                </p>
                <p class="test-class">This is inline text with <sub>subscript</sub> and <sup>superscript</sup> elements.</p>
                <p class="test-class">
                    <var>f</var>(<var>x</var>) = <var>a</var><sub>0</sub> + <var>a</var><sub>1</sub><var>x</var> +
                    <var>a</var><sub>2</sub><var>x</var><sup>2</sup>, where <var>a</var><sup>2</sup> ≠ 0
                </p>
                <time datetime="2013-09-07" pubdate>07 September 2013</time>
            </section>

            <hr />

            <section>
                <figure>
                    <img src="https://placehold.co/600x400" alt="Figure Example">
                    <figcaption>
                        Photo of the sky at night. Original by <a href="http://flickr.com/photos/heyitsadam/">@mrmrs</a>
                    </figcaption>
                </figure>
            </section>

            <hr />

            <section>
                <!--
                http://www.w3.org/html/wg/drafts/html/master/text-level-semantics.html#the-samp-element
                -->

                <pre>
                    <code>
                        /Sites/html master  ☠ ☢
                        $  <kbd>ls -gto</kbd>

                        total 104
                        -rw-r--r--   1   10779 Jun  5 16:24 index.html
                        -rw-r--r--   1    1255 Jun  5 16:00 _config.yml
                        drwxr-xr-x  11     374 Jun  5 15:57 _site
                        -rw-r--r--   1    1597 Jun  5 14:16 README.md
                        drwxr-xr-x   5     170 Jun  5 14:15 _sass
                        -rw-r--r--   1     564 Jun  4 15:59 Rakefile
                        drwxr-xr-x   6     204 Jun  4 15:59 _includes
                        drwxr-xr-x   4     136 Jun  4 15:59 _layouts
                        drwxr-xr-x   3     102 Jun  4 15:59 _resources
                        drwxr-xr-x   3     102 Jun  4 15:59 css
                        -rw-r--r--   1    1977 Jun  4 15:59 favicon.icns
                        -rw-r--r--   1    6518 Jun  4 15:59 favicon.ico
                        -rw-r--r--   1    1250 Jun  4 15:59 touch-icon-ipad-precomposed.png
                        -rw-r--r--   1    2203 Jun  4 15:59 touch-icon-ipad-retina-precomposed.png
                        -rw-r--r--   1    1046 Jun  4 15:59 touch-icon-iphone-precomposed.png
                        -rw-r--r--   1    1779 Jun  4 15:59 touch-icon-iphone-retina-precomposed.png
                    </code>
                </pre>
                </samp>
            </section>

            <hr />

            <section>
                <h2 class="other-class">Tables</h2>
                <!--
                From the HTML spec (http://www.w3.org/TR/html401/struct/tables.html)

                TFOOT must appear before TBODY within a TABLE definition so that user agents can
                render the foot before receiving all of the (potentially numerous) rows of data.
                The following summarizes which tags are required and which may be omitted:

                The TBODY start tag is always required except when the table contains only one
                table body and no table head or foot sections. The TBODY end tag may always be
                safely omitted.

                The start tags for THEAD and TFOOT are required when the table head and foot sections
                are present respectively, but the corresponding end tags may always be safely
                omitted.

                Conforming user agent parsers must obey these rules for reasons of backward
                compatibility.
                -->
                <table>
                    <caption>This is a caption for a table</caption>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tfoot>
                        Table footer info
                    </tfoot>
                    <tbody>
                        <tr>
                            <td>#999-32ac</td>
                            <td>First Name</td>
                            <td>13 May, 2013</td>
                            <td>999 Spruce Lane, Somewhere, CA 94101</td>
                        </tr>
                        <tr>
                            <td>#888-32dd</td>
                            <td>Sample Name</td>
                            <td>17 May, 1984</td>
                            <td>999 Spruce Lane, Somewhere, CA 94101</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <hr />

            <section>
                <h2>Footer</h2>
                <footer>
                    <small>©2017 studio.bio</small>
                    <address>email@email.com</address>
                </footer>
            </section>

    </section> 
</main>

<?php // get_sidebar(); ?>

<?php get_footer(); ?>
