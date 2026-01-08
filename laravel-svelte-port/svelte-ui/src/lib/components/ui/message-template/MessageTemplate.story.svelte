<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { MessageTemplateText, MessageTemplateMedia, MessageTemplateCard, MessageTemplateCallToAction, MessageTemplateListPicker, MessageTemplateQuickReply } from './index';
</script>

<Hst.Story title="Message/Templates" icon="lucide:layout-template">
  <Hst.Variant title="Text Template">
    <div class="p-4">
      <MessageTemplateText
        header="Order Confirmation"
        body="Thank you for your order #12345!\n\nYour order has been confirmed and will be shipped within 2-3 business days.\n\nTotal: $99.99"
        footer="Reply HELP for assistance"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Media Template - Image">
    <div class="p-4">
      <MessageTemplateMedia
        type="image"
        url="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400"
        caption="Check out our new product! Available now with 20% off."
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Media Template - Document">
    <div class="p-4">
      <MessageTemplateMedia
        type="document"
        url="/invoice.pdf"
        filename="Invoice_12345.pdf"
        caption="Here's your invoice for the recent purchase."
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Card Template">
    <div class="p-4">
      <MessageTemplateCard
        imageUrl="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400"
        title="Wireless Headphones"
        subtitle="Premium sound quality with 30-hour battery life. Free shipping on orders over $50."
        buttons={[
          { id: '1', title: 'View Product', type: 'url' },
          { id: '2', title: 'Add to Cart', type: 'postback' }
        ]}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Call to Action">
    <div class="p-4">
      <MessageTemplateCallToAction
        text="Need help with your order? Our support team is here to assist you 24/7."
        buttons={[
          { id: '1', title: 'Visit Website', url: 'https://example.com', type: 'url' },
          { id: '2', title: 'Call Support', type: 'call' }
        ]}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="List Picker">
    <div class="p-4">
      <MessageTemplateListPicker
        headerText="Product Catalog"
        bodyText="Browse our latest products and find something you'll love!"
        buttonText="View Products"
        sections={[
          {
            title: 'Electronics',
            items: [
              { id: '1', title: 'Wireless Earbuds', description: '$79.99' },
              { id: '2', title: 'Smart Watch', description: '$199.99' },
              { id: '3', title: 'Bluetooth Speaker', description: '$49.99' }
            ]
          },
          {
            title: 'Accessories',
            items: [
              { id: '4', title: 'Phone Case', description: '$19.99' },
              { id: '5', title: 'Charging Cable', description: '$14.99' }
            ]
          }
        ]}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Quick Reply">
    <div class="p-4">
      <MessageTemplateQuickReply
        text="How would you rate your experience with us today?"
        buttons={[
          { id: '1', title: '😊 Great' },
          { id: '2', title: '😐 Okay' },
          { id: '3', title: '😞 Poor' },
          { id: '4', title: 'Talk to Agent' }
        ]}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
