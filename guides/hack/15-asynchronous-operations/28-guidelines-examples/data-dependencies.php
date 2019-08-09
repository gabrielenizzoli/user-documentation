<?hh // strict

namespace Hack\UserDocumentation\AsyncOps\Guidelines\Examples\DataDependencies;
use namespace HH\Lib\Vec;

// So we can use function Vec\map_async()
require __DIR__."/../../../../vendor/hh_autoload.php";

class PostData {
  // using constructor argument promotion
  public function __construct(public string $text) {}
}

async function fetch_all_post_ids_for_author(
  int $author_id,
): Awaitable<vec<int>> {

  // Query database, etc., but for now, just return made up stuff
  return vec[4, 53, 99];
}

async function fetch_post_data(int $post_id): Awaitable<PostData> {
  // Query database, etc. but for now, return something random
  return new PostData(\str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"));
}

async function fetch_comment_count(int $post_id): Awaitable<int> {
  // Query database, etc., but for now, return something random
  return \rand(0, 50);
}

async function fetch_page_data(
  int $author_id,
): Awaitable<vec<(PostData, int)>> {

  $all_post_ids = await fetch_all_post_ids_for_author($author_id);
  // An async closure that will turn a post ID into a tuple of
  // post data and comment count
  $post_fetcher = async function(int $post_id): Awaitable<(PostData, int)> {
    list($post_data, $comment_count) = await Vec\from_async(vec[
      fetch_post_data($post_id),
      fetch_comment_count($post_id),
    ]);
    invariant($post_data is PostData, "This is good");
    invariant($comment_count is int, "This is good");
    return tuple($post_data, $comment_count);
  };

  // Transform the array of post IDs into an vec of results,
  // using the Vec\map_async function
  return await Vec\map_async($all_post_ids, $post_fetcher);
}

async function generate_page(int $author_id): Awaitable<string> {
  $tuples = await fetch_page_data($author_id);
  $page = "";
  foreach ($tuples as $tuple) {
    list($post_data, $comment_count) = $tuple;
    // Normally render the data into HTML, but for now, just create a
    // normal string
    $page .= $post_data->text." ".$comment_count.\PHP_EOL;
  }
  return $page;
}

<<__EntryPoint>>
function main(): void {
  print \HH\Asio\join(generate_page(13324)); // just made up a user id
}
